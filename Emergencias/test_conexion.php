<?php
echo "<h2>🔍 Diagnóstico de Conexión MySQL</h2>";

$configs = [
    'Privada' => ['host' => 'localhost', 'dbname' => 'vital_monitor_private', 'username' => 'root', 'password' => ''],
    'Pública' => ['host' => 'localhost', 'dbname' => 'vital_monitor_public', 'username' => 'root', 'password' => '']
];

foreach ($configs as $nombre => $config) {
    echo "<h3>Probando conexión: $nombre</h3>";
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']}",
            $config['username'],
            $config['password']
        );
        echo "✅ <strong>Conexión exitosa</strong> a {$config['dbname']}<br>";
        
        // Verificar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tablas encontradas: " . implode(', ', $tablas) . "<br>";
        
    } catch (PDOException $e) {
        echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br>";
        
        // Intentar sin base de datos específica
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']}",
                $config['username'],
                $config['password']
            );
            echo "⚠️  Conexión al servidor OK, pero la base de datos '{$config['dbname']}' no existe<br>";
            
            // Mostrar bases disponibles
            $stmt = $pdo->query("SHOW DATABASES");
            $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "Bases disponibles: " . implode(', ', $dbs) . "<br>";
            
        } catch (PDOException $e2) {
            echo "❌ <strong>Error grave:</strong> No se puede conectar al servidor MySQL<br>";
        }
    }
    echo "<hr>";
}
?>

<a href="panel_sync.php">← Volver al Panel de Sincronización</a>