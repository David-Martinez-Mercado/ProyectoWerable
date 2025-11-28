<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test Simple del Sistema</h1>";

// Test básico de PHP
echo "<h2>1. PHP Funcionando</h2>";
echo "✅ PHP version: " . phpversion() . "<br>";
echo "✅ Session ID: " . session_id() . "<br>";

// Test de includes básicos
echo "<h2>2. Includes Básicos</h2>";
try {
    require_once 'config/connection.php';
    echo "✅ connection.php incluido<br>";
    
    $database = new Database();
    echo "✅ Database instanciado<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

// Test directo de alertas
echo "<h2>3. Test Directo de Alertas</h2>";
try {
    require_once 'models/AlertModel.php';
    $alertModel = new AlertModel();
    echo "✅ AlertModel creado<br>";
    
    // Test simple sin BD
    echo "✅ Sistema básico funcionando<br>";
    
} catch (Exception $e) {
    echo "❌ Error en AlertModel: " . $e->getMessage() . "<br>";
    echo "📍 Archivo: " . $e->getFile() . "<br>";
    echo "📍 Línea: " . $e->getLine() . "<br>";
}

echo "<h2>4. Prueba desde JavaScript</h2>";
?>
<button onclick="testAlert()">Probar Alerta Médica</button>
<script>
function testAlert() {
    console.log('Iniciando test...');
    
    fetch('api/alerts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=medical&device=ESP32-001'
    })
    .then(response => {
        console.log('Status:', response.status);
        if (!response.ok) {
            throw new Error('HTTP ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Respuesta:', data);
        alert('ÉXITO: ' + JSON.stringify(data));
    })
    .catch(error => {
        console.error('Error:', error);
        alert('ERROR: ' + error.message);
    });
}
</script>