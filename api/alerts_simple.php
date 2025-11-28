<?php
// api/alerts_simple.php - CON RUTA CORREGIDA
header('Content-Type: application/json');

try {
    // Conexión directa
    $pdo = new PDO("mysql:host=localhost;dbname=vital_monitor_private", "root", "");
    
    // Datos POST
    $action = $_POST['action'] ?? 'medical';
    $deviceCode = $_POST['device'] ?? 'ESP32-001';
    
    if (empty($action) || empty($deviceCode)) {
        throw new Exception('Datos incompletos');
    }
    
    // Determinar tipo de alerta
    if ($action === 'medical') {
        $tipo = 'medica';
        $descripcion = 'Emergencia médica activada';
        $base_message = '🚨 Alerta médica activada. Ayuda en camino.';
    } else {
        $tipo = 'extravio';
        $descripcion = 'Paciente extraviado';
        $base_message = '⚠️ Alerta de extravío activada. Buscando paciente.';
    }
    
    // Insertar alerta
    $stmt = $pdo->prepare("INSERT INTO log_alertas (id_dispositivo, tipo_alerta, descripcion, estado) VALUES (?, ?, ?, 'PENDIENTE')");
    $stmt->execute([$deviceCode, $tipo, $descripcion]);
    $alertId = $pdo->lastInsertId();
    
    // 🔥 SINCRONIZACIÓN AUTOMÁTICA SEGURA - RUTA CORREGIDA
    $sync_status = "pending";
    $sync_message = "";
    
    try {
        // RUTA CORREGIDA: ../Emergencias/sync_system/
        $sync_path = '../Emergencias/sync_system/sync_c5_api.php';
        
        if (file_exists($sync_path)) {
            require_once $sync_path;
            
            if (class_exists('SincronizadorC5')) {
                $sincronizador = new SincronizadorC5();
                
                // Pequeño delay para consistencia
                usleep(300000); // 0.3 segundos
                
                $id_c5 = $sincronizador->enviarAlertaC5($alertId);
                
                if ($id_c5) {
                    $sync_status = "success";
                    $sync_message = " ✅ Enviada a emergencias";
                } else {
                    $sync_status = "warning";
                    $sync_message = " ⚠️ Creada - Sincronización pendiente";
                }
            } else {
                $sync_status = "warning";
                $sync_message = " ⚠️ Creada - Clase C5 no disponible";
            }
        } else {
            $sync_status = "warning";
            $sync_message = " ⚠️ Creada - Sync system no encontrado en: " . $sync_path;
        }
        
    } catch (Exception $sync_error) {
        // Error en sincronización NO afecta la alerta principal
        error_log("Error sincronización C5: " . $sync_error->getMessage());
        $sync_status = "warning";
        $sync_message = " ⚠️ Creada - Error en sincronización";
    }
    
    // Respuesta final
    echo json_encode([
        'success' => true,
        'message' => $base_message . $sync_message,
        'alert_id' => $alertId,
        'alert_type' => $tipo,
        'sync_status' => $sync_status,
        'sync_message' => $sync_message
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>