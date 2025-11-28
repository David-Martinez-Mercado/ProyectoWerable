<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Conexión a Bases de Datos</h1>";

try {
    require_once 'config/connection.php';
    $database = new Database();
    
    echo "<h2>✅ Conexión a BD Privada: OK</h2>";
    
    // Test BD Pública
    $stmt = $database->conn_public->query("SELECT COUNT(*) as count FROM alertas_c5");
    $result = $stmt->fetch();
    echo "<h2>✅ Conexión a BD Pública: OK</h2>";
    echo "<p>Registros en alertas_c5: " . $result['count'] . "</p>";
    
    // Test estructura de tabla
    $stmt = $database->conn_public->query("DESCRIBE alertas_c5");
    $columns = $stmt->fetchAll();
    echo "<h3>Columnas de alertas_c5:</h3>";
    foreach ($columns as $col) {
        echo "<p>{$col['Field']} - {$col['Type']}</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error: " . $e->getMessage() . "</h2>";
}
?>