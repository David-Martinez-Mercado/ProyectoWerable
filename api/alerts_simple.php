<?php
// api/alerts_simple.php - VERSIÓN SUPER SIMPLE QUE SÍ FUNCIONA
header('Content-Type: application/json');

try {
    // Conexión directa SIN session ni includes
    $pdo = new PDO("mysql:host=localhost;dbname=vital_monitor_private", "root", "");
    
    // Datos básicos
    $action = 'medical'; // Valor fijo para probar
    $deviceCode = 'ESP32-001'; // Valor fijo para probar
    
    // INSERT simple
    $stmt = $pdo->prepare("INSERT INTO log_alertas (id_dispositivo, tipo_alerta, descripcion, estado) VALUES (?, ?, ?, 'PENDIENTE')");
    $stmt->execute([$deviceCode, 'medica', 'Alerta médica activada']);
    
    echo json_encode([
        'success' => true,
        'message' => '🚨 Alerta médica activada. Ayuda en camino.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>