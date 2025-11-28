<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular sesión para testing
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

header('Content-Type: application/json');

echo "=== DEBUG ALERTS API ===\n";

try {
    // 1. Verificar método
    $method = $_SERVER['REQUEST_METHOD'];
    echo "Método: $method\n";
    
    // 2. Verificar datos POST
    echo "Datos POST:\n";
    print_r($_POST);
    
    echo "Datos GET:\n";
    print_r($_GET);
    
    // 3. Incluir modelos
    echo "Incluyendo modelos...\n";
    require_once '../models/AlertModel.php';
    require_once '../models/DeviceModel.php';
    require_once '../models/LecturaModel.php';
    echo "Modelos incluidos OK\n";
    
    // 4. Instanciar modelos
    $alertModel = new AlertModel();
    $deviceModel = new DeviceModel();
    echo "Modelos instanciados OK\n";
    
    // 5. Procesar la alerta
    $action = $_POST['action'] ?? 'medical';
    $deviceCode = $_POST['device'] ?? 'ESP32-001';
    
    echo "Acción: $action\n";
    echo "Dispositivo: $deviceCode\n";
    
    // Verificar dispositivo
    $device = $deviceModel->getDevice($deviceCode, $_SESSION['user_id']);
    if (!$device) {
        throw new Exception("Dispositivo no encontrado: $deviceCode");
    }
    echo "Dispositivo verificado: " . $device['nombre_paciente'] . "\n";
    
    // Obtener última ubicación
    $lecturaModel = new LecturaModel();
    $lastReading = $lecturaModel->getLastReading($deviceCode);
    $latitude = $lastReading['gps_lat'] ?? 19.4326;
    $longitude = $lastReading['gps_lon'] ?? -99.1332;
    
    echo "Ubicación: $latitude, $longitude\n";
    
    // Crear alerta
    if ($action === 'medical') {
        $alertId = $alertModel->createMedicalAlert($deviceCode, $latitude, $longitude);
        $message = 'Alerta médica activada';
    } else {
        $alertId = $alertModel->createMissingAlert($deviceCode, $latitude, $longitude);
        $message = 'Alerta de extravío activada';
    }
    
    echo "Alerta creada ID: $alertId\n";
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => $message,
        'alert_id' => $alertId,
        'debug' => 'Proceso completado correctamente'
    ]);
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTrace()
        ]
    ]);
}
?>