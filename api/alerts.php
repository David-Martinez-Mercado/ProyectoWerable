<?php
session_start();
require_once '../models/AlertModel.php';
require_once '../models/DeviceModel.php';
require_once '../models/LecturaModel.php';

// Configurar para mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Log para debugging
error_log("=== ALERTS.PH P ACCEDIDO ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("SESSION user_id: " . ($_SESSION['user_id'] ?? 'NO_HAY_SESION'));

if (!isset($_SESSION['user_id'])) {
    error_log("ERROR: Usuario no autenticado");
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'No autorizado. Por favor inicie sesión.',
        'session_status' => 'no_session'
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$alertModel = new AlertModel();
$deviceModel = new DeviceModel();

try {
    switch ($method) {
        case 'GET':
            handleGetAlerts($alertModel, $_SESSION['user_id']);
            break;
            
        case 'POST':
            handlePostAlert($alertModel, $deviceModel, $_SESSION['user_id']);
            break;
            
        case 'PUT':
            handlePutAlert($alertModel);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false, 
                'message' => 'Método no permitido'
            ]);
    }
} catch (Exception $e) {
    error_log("ERROR en alerts.php: " . $e->getMessage());
    error_log("Archivo: " . $e->getFile() . " Línea: " . $e->getLine());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage(),
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}

function handleGetAlerts($alertModel, $userId) {
    $deviceCode = $_GET['device'] ?? '';
    
    error_log("GET Alerts - Device: $deviceCode, User: $userId");
    
    if (isset($_GET['status']) && $_GET['status'] === 'true') {
        $activeAlerts = $alertModel->getActiveAlerts($userId);
        error_log("Alertas activas encontradas: " . count($activeAlerts));
        
        echo json_encode([
            'success' => true, 
            'activeAlerts' => $activeAlerts,
            'count' => count($activeAlerts)
        ]);
        return;
    }
    
    $activeAlerts = $alertModel->getActiveAlerts($userId);
    echo json_encode([
        'success' => true, 
        'activeAlerts' => $activeAlerts
    ]);
}

function handlePostAlert($alertModel, $deviceModel, $userId) {
    // Obtener datos de POST
    $action = $_POST['action'] ?? '';
    $deviceCode = $_POST['device'] ?? '';
    
    error_log("POST Alert - Action: $action, Device: $deviceCode, User: $userId");
    
    // Log todos los datos POST para debug
    error_log("Datos POST completos: " . print_r($_POST, true));
    
    if (empty($deviceCode) || empty($action)) {
        error_log("ERROR: Datos incompletos");
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Datos incompletos. Se requiere action y device.',
            'received_data' => $_POST
        ]);
        return;
    }
    
    // Verificar que el dispositivo pertenece al usuario
    error_log("Buscando dispositivo: $deviceCode para usuario: $userId");
    $device = $deviceModel->getDevice($deviceCode, $userId);
    
    if (!$device) {
        error_log("ERROR: Dispositivo no encontrado o no pertenece al usuario");
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'message' => 'Dispositivo no encontrado o no tiene permisos',
            'device_code' => $deviceCode
        ]);
        return;
    }
    
    error_log("Dispositivo encontrado: " . $device['nombre_paciente']);
    
    // Obtener última ubicación del dispositivo
    $lecturaModel = new LecturaModel();
    $lastReading = $lecturaModel->getLastReading($deviceCode);
    
    $latitude = $lastReading['gps_lat'] ?? 19.4326;
    $longitude = $lastReading['gps_lon'] ?? -99.1332;
    
    error_log("Ubicación obtenida: $latitude, $longitude");
    
    // Crear la alerta según el tipo
    switch ($action) {
        case 'medical':
            error_log("Creando alerta MÉDICA");
            $alertId = $alertModel->createMedicalAlert($deviceCode, $latitude, $longitude);
            $message = '🚨 Alerta médica activada. Ayuda en camino.';
            $alertType = 'médica';
            break;
            
        case 'missing':
            error_log("Creando alerta de EXTRAVÍO");
            $alertId = $alertModel->createMissingAlert($deviceCode, $latitude, $longitude);
            $message = '⚠️ Alerta de extravío activada. Buscando paciente.';
            $alertType = 'extravío';
            break;
            
        default:
            error_log("ERROR: Tipo de alerta no válido: $action");
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Tipo de alerta no válido. Use "medical" o "missing".',
                'received_action' => $action
            ]);
            return;
    }
    
    error_log("Alerta creada exitosamente. ID: $alertId");
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'alert_id' => $alertId,
        'alert_type' => $alertType,
        'location' => [
            'lat' => $latitude,
            'lng' => $longitude
        ],
        'patient' => [
            'name' => $device['nombre_paciente'],
            'device' => $deviceCode
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function handlePutAlert($alertModel) {
    $alertId = $_GET['id'] ?? '';
    
    if (empty($alertId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'ID de alerta requerido'
        ]);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $status = $data['status'] ?? '';
    $notes = $data['notes'] ?? '';
    
    if (empty($status)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Estado requerido'
        ]);
        return;
    }
    
    $validStatuses = ['PENDIENTE', 'EN PROCESO', 'EN LUGAR', 'RESUELTA', 'CANCELADA'];
    if (!in_array($status, $validStatuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Estado no válido. Use: ' . implode(', ', $validStatuses)
        ]);
        return;
    }
    
    $alertModel->updateAlertStatus($alertId, $status, $notes);
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado de alerta actualizado',
        'alert_id' => $alertId,
        'new_status' => $status
    ]);
}
?>