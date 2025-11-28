<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Para testing, simular sesión si no existe
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Usuario Test';
}

try {
    $action = $_POST['action'] ?? '';
    $deviceCode = $_POST['device'] ?? '';
    
    if (empty($action) || empty($deviceCode)) {
        throw new Exception('Datos incompletos');
    }
    
    // Conectar directamente a la base de datos
    require_once '../config/connection.php';
    $database = new Database();
    $pdo = $database->conn_private;
    
    // Verificar que el dispositivo existe
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE codigo = ?");
    $stmt->execute([$deviceCode]);
    $device = $stmt->fetch();
    
    if (!$device) {
        throw new Exception('Dispositivo no encontrado: ' . $deviceCode);
    }
    
    // Insertar alerta en la base de datos
    if ($action === 'medical') {
        $tipo = 'medica';
        $descripcion = 'Emergencia médica activada';
        $message = '🚨 Alerta médica activada. Ayuda en camino.';
    } else {
        $tipo = 'extravio';
        $descripcion = 'Paciente extraviado';
        $message = '⚠️ Alerta de extravío activada. Buscando paciente.';
    }
    
    // Obtener última ubicación
    $stmt = $pdo->prepare("SELECT gps_lat, gps_lon FROM lecturas WHERE id_dispositivo = ? ORDER BY fecha_lectura DESC LIMIT 1");
    $stmt->execute([$deviceCode]);
    $location = $stmt->fetch();
    
    $latitude = $location['gps_lat'] ?? 19.4326;
    $longitude = $location['gps_lon'] ?? -99.1332;
    
    // Insertar alerta
    $stmt = $pdo->prepare("INSERT INTO log_alertas (id_dispositivo, tipo_alerta, descripcion, ubicacion_lat, ubicacion_lon, estado, fecha_creacion) 
                           VALUES (?, ?, ?, ?, ?, 'PENDIENTE', NOW())");
    $stmt->execute([$deviceCode, $tipo, $descripcion, $latitude, $longitude]);
    $alertId = $pdo->lastInsertId();
    
    // Respuesta final SIN sincronización automática
    echo json_encode([
        'success' => true,
        'message' => $message,
        'alert_id' => $alertId,
        'alert_type' => $tipo,
        'patient' => $device['nombre_paciente'],
        'location' => [
            'lat' => $latitude,
            'lng' => $longitude
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>