<?php
// Mostrar TODOS los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Estado del Sistema y Errores</h1>";

// Verificar si hay errores en el buffer
if (ob_get_level()) ob_end_clean();

// Test de alertas con manejo detallado de errores
echo "<h2>Test de AlertModel</h2>";

try {
    echo "Incluyendo BaseModel...<br>";
    require_once 'models/BaseModel.php';
    echo "✅ BaseModel incluido<br>";
    
    echo "Incluyendo AlertModel...<br>";
    require_once 'models/AlertModel.php';
    echo "✅ AlertModel incluido<br>";
    
    echo "Creando instancia...<br>";
    $model = new AlertModel();
    echo "✅ AlertModel instanciado<br>";
    
    echo "✅ TODO FUNCIONA CORRECTAMENTE<br>";
    
} catch (Throwable $e) {
    echo "<h2 style='color: red;'>❌ ERROR ENCONTRADO:</h2>";
    echo "<strong>Mensaje:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Línea:</strong> " . $e->getLine() . "<br>";
    echo "<strong>Tipo:</strong> " . get_class($e) . "<br>";
    echo "<strong>Trace:</strong><pre>" . $e->getTraceAsString() . "</pre>";
}

// Verificar archivos existentes
echo "<h2>Archivos del Sistema</h2>";
$files = [
    'config/connection.php',
    'models/BaseModel.php', 
    'models/AlertModel.php',
    'models/DeviceModel.php',
    'models/LecturaModel.php',
    'api/alerts.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file NO existe<br>";
    }
}
?>