<?php
session_start();
require_once __DIR__ . '/../models/LecturaModel.php';
require_once __DIR__ . '/../models/DeviceModel.php';

header('Content-Type: application/json');

// ✅ AGREGAR LOGGING DE INICIO
error_log("=== readings.php INICIADO ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("GET params: " . json_encode($_GET));

// Verificar API key para dispositivos ESP32
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
$isDeviceRequest = !empty($apiKey) && $apiKey === 'TU_API_KEY_SECRETA_ESP32';

error_log("API Key recibida: " . ($apiKey ? 'SÍ' : 'NO'));
error_log("Es dispositivo: " . ($isDeviceRequest ? 'SÍ' : 'NO'));
error_log("User ID en sesión: " . ($_SESSION['user_id'] ?? 'NO'));

if (!isset($_SESSION['user_id']) && !$isDeviceRequest) {
    error_log("❌ ERROR: No autorizado - Sin sesión ni API key válida");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$lecturaModel = new LecturaModel();
$deviceModel = new DeviceModel();

error_log("Modelos cargados correctamente");

try {
    switch ($method) {
        case 'GET':
            error_log("📥 Procesando GET request");
            handleGetReadings($lecturaModel, $deviceModel, $isDeviceRequest);
            break;
            
        case 'POST':
            error_log("📥 Procesando POST request");
            handlePostReading($lecturaModel, $isDeviceRequest);
            break;
            
        default:
            error_log("❌ Método no permitido: " . $method);
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    error_log("❌ ERROR en readings.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function handleGetReadings($lecturaModel, $deviceModel, $isDeviceRequest) {
    $deviceCode = $_GET['device'] ?? '';
    
    error_log("🔍 Dispositivo solicitado: " . $deviceCode);
    
    if (empty($deviceCode)) {
        error_log("❌ ERROR: Código de dispositivo vacío");
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Código de dispositivo requerido']);
        return;
    }
    
    // Para usuarios, verificar que el dispositivo les pertenece
    if (!$isDeviceRequest) {
        error_log("👤 Verificando permisos de usuario para dispositivo: " . $deviceCode);
        $device = $deviceModel->getDevice($deviceCode, $_SESSION['user_id']);
        if (!$device) {
            error_log("❌ ERROR: Dispositivo no encontrado o sin permisos");
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
            return;
        }
        error_log("✅ Usuario tiene permisos para el dispositivo");
    }
    
    // Exportar CSV
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        error_log("📊 Solicitando exportación CSV");
        exportToCSV($lecturaModel, $deviceCode);
        return;
    }
    
    // Datos para gráficas
    if (isset($_GET['chart']) && $_GET['chart'] === 'true') {
        $hours = $_GET['hours'] ?? 6;
        error_log("📈 Solicitando datos de gráfica - Horas: " . $hours);
        
        $chartData = $lecturaModel->getChartData($deviceCode, $hours);
        error_log("📊 Datos de gráfica obtenidos: " . count($chartData) . " registros");
        
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
        
        error_log("✅ Enviando datos de gráfica formateados");
        echo json_encode(['success' => true, 'chartData' => $formattedData]);
        return;
    }
    
    // Historial
    if (isset($_GET['history']) && $_GET['history'] === 'true') {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        error_log("📋 Solicitando historial - Desde: " . $startDate . " Hasta: " . $endDate);
        
        $historicalData = $lecturaModel->getHistoricalData($deviceCode, $startDate, $endDate);
        error_log("📋 Datos históricos obtenidos: " . count($historicalData) . " registros");
        
        echo json_encode(['success' => true, 'historicalData' => $historicalData]);
        return;
    }
    
    // ✅ Última lectura - CON DEBUGGING EXTENDIDO
    error_log("🔍 Buscando última lectura para dispositivo: " . $deviceCode);
    $lastReading = $lecturaModel->getLastReading($deviceCode);
    
    // ✅ AGREGAR LOG DETALLADO DE LA RESPUESTA
    error_log("📖 Última lectura obtenida: " . json_encode($lastReading));
    
    if ($lastReading) {
        error_log("✅ Enviando última lectura al frontend");
        echo json_encode([
            'success' => true, 
            'reading' => $lastReading,
            'debug' => [ // ✅ Opcional: datos de debug para desarrollo
                'device' => $deviceCode,
                'timestamp' => date('Y-m-d H:i:s'),
                'records_found' => true
            ]
        ]);
    } else {
        error_log("⚠️ No se encontraron lecturas para el dispositivo");
        echo json_encode([
            'success' => true, 
            'reading' => null,
            'debug' => [ // ✅ Opcional: datos de debug para desarrollo
                'device' => $deviceCode,
                'timestamp' => date('Y-m-d H:i:s'),
                'records_found' => false
            ]
        ]);
    }
    
    error_log("✅ handleGetReadings completado exitosamente");
}

// ... (el resto de las funciones handlePostReading y exportToCSV se mantienen igual)
function handlePostReading($lecturaModel, $isDeviceRequest) {
    error_log("📤 Procesando POST para nueva lectura");
    
    if (!$isDeviceRequest) {
        error_log("❌ ERROR: Solo dispositivos pueden enviar lecturas");
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Solo dispositivos pueden enviar lecturas']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    error_log("📦 Datos POST recibidos: " . json_encode($data));
    
    // ... resto del código igual
}

function exportToCSV($lecturaModel, $deviceCode) {
    error_log("💾 Iniciando exportación CSV para: " . $deviceCode);
    // ... resto del código igual
}
?>