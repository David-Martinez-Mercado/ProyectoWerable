<?php
session_start();
require_once '../models/DeviceModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$deviceModel = new DeviceModel();

try {
    switch ($method) {
        case 'GET':
            handleGetDevices($deviceModel, $_SESSION['user_id']);
            break;
            
        case 'POST':
            handlePostDevice($deviceModel, $_SESSION['user_id']);
            break;
            
        case 'PUT':
            handlePutDevice($deviceModel, $_SESSION['user_id']);
            break;
            
        case 'DELETE':
            handleDeleteDevice($deviceModel, $_SESSION['user_id']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function handleGetDevices($deviceModel, $userId) {
    $deviceCode = $_GET['id'] ?? '';
    
    if ($deviceCode) {
        $device = $deviceModel->getDevice($deviceCode, $userId);
        if ($device) {
            echo json_encode(['success' => true, 'device' => $device]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
        }
    } else {
        $devices = $deviceModel->getUserDevices($userId);
        echo json_encode(['success' => true, 'devices' => $devices]);
    }
}

function handlePostDevice($deviceModel, $userId) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $deviceCode = $data['deviceCode'] ?? '';
    $patientName = $data['patientName'] ?? '';
    $birthDate = $data['birthDate'] ?? '';
    $age = $data['age'] ?? '';
    $address = $data['address'] ?? '';
    $collectionAddress = $data['collectionAddress'] ?? '';
    $emergencyContact = $data['emergencyContact'] ?? '';
    $conditions = $data['conditions'] ?? '';
    $thresholds = $data['thresholds'] ?? [];
    
    if (empty($deviceCode) || empty($patientName) || empty($birthDate) || empty($age) || empty($address) || empty($emergencyContact)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios son requeridos']);
        return;
    }
    
    if ($deviceModel->deviceExists($deviceCode)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El código del dispositivo ya está registrado']);
        return;
    }
    
    $affected = $deviceModel->addDevice($userId, $deviceCode, $patientName, $birthDate, $address, $collectionAddress, $emergencyContact, $age, $conditions, $thresholds);
    
    if ($affected > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Dispositivo agregado exitosamente',
            'device_code' => $deviceCode
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al agregar el dispositivo']);
    }
}

function handlePutDevice($deviceModel, $userId) {
    $deviceCode = $_GET['id'] ?? '';
    
    if (empty($deviceCode)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID del dispositivo requerido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $thresholds = $data['thresholds'] ?? null;
    
    if ($thresholds) {
        // Actualizar umbrales
        $device = $deviceModel->getDevice($deviceCode, $userId);
        if (!$device) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
            return;
        }
        
        $affected = $deviceModel->updateDeviceThresholds($deviceCode, $thresholds);
        
        if ($affected > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Umbrales actualizados correctamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar los umbrales']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos de umbrales requeridos']);
    }
}

function handleDeleteDevice($deviceModel, $userId) {
    $deviceCode = $_GET['id'] ?? '';
    
    if (empty($deviceCode)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID del dispositivo requerido']);
        return;
    }
    
    $device = $deviceModel->getDevice($deviceCode, $userId);
    if (!$device) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
        return;
    }
    
    $affected = $deviceModel->updateDeviceStatus($deviceCode, 'inactivo');
    
    if ($affected > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Dispositivo desactivado exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al desactivar el dispositivo']);
    }
}
?>