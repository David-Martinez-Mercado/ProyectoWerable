<?php
session_start();
require_once '../models/AlertModel.php';
require_once '../models/DeviceModel.php';
require_once '../models/LecturaModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$alertModel = new AlertModel();
$deviceModel = new DeviceModel();

$action = $_GET['action'] ?? '';
$deviceCode = $_GET['device'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetAlerts($alertModel, $_SESSION['user_id']);
            break;
            
        case 'POST':
            handlePostAlert($alertModel, $deviceModel, $_SESSION['user_id'], $action, $deviceCode);
            break;
            
        case 'PUT':
            handlePutAlert($alertModel);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function handleGetAlerts($alertModel, $userId) {
    $deviceCode = $_GET['device'] ?? '';
    
    if (isset($_GET['status']) && $_GET['status'] === 'true') {
        $activeAlerts = $alertModel->getActiveAlerts($userId);
        echo json_encode(['success' => true, 'activeAlerts' => $activeAlerts]);
        return;
    }
    
    if (isset($_GET['history']) && $_GET['history'] === 'true') {
        echo json_encode(['success' => true, 'history' => []]);
        return;
    }
    
    $activeAlerts = $alertModel->getActiveAlerts($userId);
    echo json_encode(['success' => true, 'activeAlerts' => $activeAlerts]);
}

function handlePostAlert($alertModel, $deviceModel, $userId, $action, $deviceCode) {
    if (empty($deviceCode)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Código de dispositivo requerido']);
        return;
    }
    
    // Verificar que el dispositivo pertenece al usuario
    $device = $deviceModel->getDevice($deviceCode, $userId);
    if (!$device) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
        return;
    }
    
    // Obtener última ubicación del dispositivo
    $lecturaModel = new LecturaModel();
    $lastReading = $lecturaModel->getLastReading($deviceCode);
    
    $latitude = $lastReading['gps_lat'] ?? 19.4326;
    $longitude = $lastReading['gps_lon'] ?? -99.1332;
    
    switch ($action) {
        case 'medical':
            $alertId = $alertModel->createMedicalAlert($deviceCode, $latitude, $longitude);
            $message = '🚨 Alerta médica activada. Ayuda en camino.';
            break;
            
        case 'missing':
            $alertId = $alertModel->createMissingAlert($deviceCode, $latitude, $longitude);
            $message = '⚠️ Alerta de extravío activada. Buscando paciente.';
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tipo de alerta no válido']);
            return;
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'alert_id' => $alertId,
        'location' => [
            'lat' => $latitude,
            'lng' => $longitude
        ]
    ]);
}

function handlePutAlert($alertModel) {
    $alertId = $_GET['id'] ?? '';
    
    if (empty($alertId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de alerta requerido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $status = $data['status'] ?? '';
    $notes = $data['notes'] ?? '';
    
    if (empty($status)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Estado requerido']);
        return;
    }
    
    $validStatuses = ['PENDIENTE', 'EN PROCESO', 'EN LUGAR', 'RESUELTA', 'CANCELADA'];
    if (!in_array($status, $validStatuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Estado no válido']);
        return;
    }
    
    $alertModel->updateAlertStatus($alertId, $status, $notes);
    
    echo json_encode([
        'success' => true,
        'message' => 'Estado de alerta actualizado'
    ]);
}
?>  