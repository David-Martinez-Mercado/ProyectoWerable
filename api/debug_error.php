<?php
// api/debug_error.php - Para ver el error real
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "=== INICIANDO DEBUG PHP ===<br>";

// 1. Probar session_start()
echo "1. Probando session_start()...<br>";
session_start();
echo "✅ session_start() OK<br>";

// 2. Probar headers
echo "2. Probando headers...<br>";
header('Content-Type: application/json');
echo "✅ headers OK<br>";

// 3. Probar conexión a BD
echo "3. Probando conexión a BD...<br>";
try {
    $pdo = new PDO("mysql:host=localhost;dbname=vital_monitor_private", "root", "");
    echo "✅ Conexión BD OK<br>";
} catch (PDOException $e) {
    echo "❌ Error BD: " . $e->getMessage() . "<br>";
    exit;
}

// 4. Probar INSERT
echo "4. Probando INSERT...<br>";
try {
    $stmt = $pdo->prepare("INSERT INTO log_alertas (id_dispositivo, tipo_alerta, descripcion, estado) VALUES (?, ?, ?, 'PENDIENTE')");
    $stmt->execute(['ESP32-001', 'medica', 'Prueba debug']);
    $alertId = $pdo->lastInsertId();
    echo "✅ INSERT OK - ID: $alertId<br>";
} catch (Exception $e) {
    echo "❌ Error INSERT: " . $e->getMessage() . "<br>";
    exit;
}

echo "=== DEBUG COMPLETADO ===<br>";
echo json_encode(['success' => true, 'message' => 'Debug exitoso']);
?>