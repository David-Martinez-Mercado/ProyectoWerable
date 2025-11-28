<?php
session_start();
require_once __DIR__ . '/../models/LecturaModel.php';
require_once __DIR__ . '/../models/DeviceModel.php';

header('Content-Type: application/json');

// Verificar API key para dispositivos ESP32
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$isDeviceRequest = !empty($apiKey) && $apiKey === 'TU_API_KEY_SECRETA_ESP32';

if (!isset($_SESSION['user_id']) && !$isDeviceRequest) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$lecturaModel = new LecturaModel();
$deviceModel = new DeviceModel();

try {
    switch ($method) {
        case 'GET':
            handleGetReadings($lecturaModel, $deviceModel, $isDeviceRequest);
            break;
            
        case 'POST':
            handlePostReading($lecturaModel, $isDeviceRequest);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function handleGetReadings($lecturaModel, $deviceModel, $isDeviceRequest) {
    $deviceCode = $_GET['device'] ?? '';
    
    if (empty($deviceCode)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Código de dispositivo requerido']);
        return;
    }
    
    // Para usuarios, verificar que el dispositivo les pertenece
    if (!$isDeviceRequest) {
        $device = $deviceModel->getDevice($deviceCode, $_SESSION['user_id']);
        if (!$device) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
            return;
        }
    }
    
    // Exportar CSV
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        exportToCSV($lecturaModel, $deviceCode);
        return;
    }
    
    // Datos para gráficas
    if (isset($_GET['chart']) && $_GET['chart'] === 'true') {
        $hours = $_GET['hours'] ?? 6;
        $chartData = $lecturaModel->getChartData($deviceCode, $hours);
        
        $formattedData = [
            'labels' => [],
            'heartRate' => [],
            'spO2' => [],
            'temperature' => []
        ];
        
        foreach ($chartData as $reading) {
            $formattedData['labels'][] = date('H:i', strtotime($reading['fecha_lectura']));
            $formattedData['heartRate'][] = $reading['lectura_FC'];
            $formattedData['spO2'][] = $reading['lectura_SpO2'];
            $formattedData['temperature'][] = $reading['lectura_temperatura'];
        }
        
        echo json_encode(['success' => true, 'chartData' => $formattedData]);
        return;
    }
    
    // Historial
    if (isset($_GET['history']) && $_GET['history'] === 'true') {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $historicalData = $lecturaModel->getHistoricalData($deviceCode, $startDate, $endDate);
        echo json_encode(['success' => true, 'historicalData' => $historicalData]);
        return;
    }
    
    // Última lectura
    $lastReading = $lecturaModel->getLastReading($deviceCode);
    if ($lastReading) {
        echo json_encode(['success' => true, 'reading' => $lastReading]);
    } else {
        echo json_encode(['success' => true, 'reading' => null]);
    }
}

function handlePostReading($lecturaModel, $isDeviceRequest) {
    if (!$isDeviceRequest) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo dispositivos pueden enviar lecturas']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $deviceId = $data['id_dispositivo'] ?? '';
    $heartRate = $data['lectura_FC'] ?? null;
    $spO2 = $data['lectura_SpO2'] ?? null;
    $temperature = $data['lectura_temperatura'] ?? null;
    $gpsLat = $data['gps_lat'] ?? null;
    $gpsLon = $data['gps_lon'] ?? null;
    
    if (empty($deviceId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de dispositivo requerido']);
        return;
    }
    
    // Insertar lectura
    $readingId = $lecturaModel->insertReading($deviceId, $heartRate, $spO2, $temperature, $gpsLat, $gpsLon);
    
    // Verificar alertas
    if ($heartRate !== null && $spO2 !== null && $temperature !== null) {
        $alerts = $lecturaModel->checkAlertThresholds($deviceId, $heartRate, $spO2, $temperature);
        
        if (!empty($alerts)) {
            // Aquí se podrían generar alertas automáticas
            error_log("Alertas detectadas para dispositivo $deviceId: " . implode(', ', $alerts));
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Lectura registrada exitosamente',
        'reading_id' => $readingId,
        'alerts_detected' => $alerts ?? []
    ]);
}

function exportToCSV($lecturaModel, $deviceCode) {
    $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
    $endDate = $_GET['end_date'] ?? date('Y-m-d');
    
    $historicalData = $lecturaModel->getHistoricalData($deviceCode, $startDate, $endDate);
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="lecturas_' . $deviceCode . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Headers CSV
    fputcsv($output, [
        'Fecha y Hora',
        'Frecuencia Cardíaca (lpm)',
        'Saturación de Oxígeno (%)',
        'Temperatura (°C)',
        'Latitud',
        'Longitud'
    ]);
    
    // Datos
    foreach ($historicalData as $reading) {
        fputcsv($output, [
            $reading['fecha_lectura'],
            $reading['lectura_FC'],
            $reading['lectura_SpO2'],
            $reading['lectura_temperatura'],
            $reading['gps_lat'],
            $reading['gps_lon']
        ]);
    }
    
    fclose($output);
    exit;
}
?>