<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simular sesión de usuario para testing
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

echo "<h1>Debug de Alertas</h1>";

try {
    // 1. Test de includes
    echo "<h2>1. Test de Includes</h2>";
    require_once 'models/BaseModel.php';
    echo "✅ BaseModel incluido<br>";
    
    require_once 'models/AlertModel.php';
    echo "✅ AlertModel incluido<br>";
    
    require_once 'models/DeviceModel.php';
    echo "✅ DeviceModel incluido<br>";
    
    require_once 'models/LecturaModel.php';
    echo "✅ LecturaModel incluido<br>";
    
    // 2. Test de conexión a BD
    echo "<h2>2. Test de Conexión BD</h2>";
    require_once 'config/connection.php';
    $database = new Database();
    echo "✅ Conexión BD privada: OK<br>";
    echo "✅ Conexión BD pública: OK<br>";
    
    // 3. Test de instanciación de modelos
    echo "<h2>3. Test de Modelos</h2>";
    $alertModel = new AlertModel();
    echo "✅ AlertModel instanciado<br>";
    
    $deviceModel = new DeviceModel();
    echo "✅ DeviceModel instanciado<br>";
    
    // 4. Test de datos de dispositivo
    echo "<h2>4. Test de Datos de Dispositivo</h2>";
    $device = $deviceModel->getDevice('ESP32-001', 1);
    if ($device) {
        echo "✅ Dispositivo encontrado: " . $device['nombre_paciente'] . "<br>";
    } else {
        echo "❌ Dispositivo NO encontrado<br>";
    }
    
    // 5. Test de creación de alerta médica
    echo "<h2>5. Test de Alerta Médica</h2>";
    try {
        $alertId = $alertModel->createMedicalAlert('ESP32-001', 19.432607, -99.133208);
        echo "✅ Alerta médica creada. ID: " . $alertId . "<br>";
        
        // Verificar que se creó en ambas BD
        $sqlPrivate = "SELECT * FROM Log_Alertas WHERE id = ?";
        $stmt = $database->conn_private->prepare($sqlPrivate);
        $stmt->execute([$alertId]);
        $alertPrivate = $stmt->fetch();
        echo "✅ Alerta en BD privada: " . ($alertPrivate ? 'SÍ' : 'NO') . "<br>";
        
        $sqlPublic = "SELECT * FROM alertas_c5 WHERE id_alerta_privada = ?";
        $stmt = $database->conn_public->prepare($sqlPublic);
        $stmt->execute([$alertId]);
        $alertPublic = $stmt->fetch();
        echo "✅ Alerta en BD pública: " . ($alertPublic ? 'SÍ' : 'NO') . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Error creando alerta: " . $e->getMessage() . "<br>";
        echo "📍 Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "<br>";
    }
    
    // 6. Test de alertas activas
    echo "<h2>6. Test de Alertas Activas</h2>";
    $activeAlerts = $alertModel->getActiveAlerts(1);
    echo "✅ Alertas activas: " . count($activeAlerts) . "<br>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERROR CRÍTICO</h2>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
    echo "Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>Prueba desde JavaScript:</h3>";
echo '<button onclick="testAlert()">Probar Alerta Médica</button>';
?>
<script>
function testAlert() {
    fetch('api/alerts.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=medical&device=ESP32-001'
    })
    .then(response => {
        console.log('Status:', response.status);
        return response.json();
    })
    .then(data => {
        alert('Respuesta: ' + JSON.stringify(data));
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}
</script>