<?php
// panel_sync.php - MEJORADO CON ESTADOS Y LIMPIEZA
echo "<h2>🔧 Sistema de Sincronización C5 - Con Limpieza</h2>";

if (isset($_POST['accion'])) {
    require_once 'sync_system/sync_c5_api.php';
    $sincronizador = new SincronizadorC5();
    
    echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 10px;'>";
    echo "<h3>Ejecutando: " . $_POST['accion'] . "</h3>";
    echo "<pre style='background: white; padding: 10px; border-radius: 5px;'>";
    
    switch ($_POST['accion']) {
        case 'enviar':
            $sincronizador->enviarAlertaC5($_POST['id']);
            break;
        case 'actualizar':
            $sincronizador->actualizarEstadoDesdeC5($_POST['id']);
            break;
        case 'batch':
            $sincronizador->sincronizarPendientes();
            break;
        case 'limpiar':  // 🔥 NUEVO
            $resultado = $sincronizador->limpiarYActualizarAlertas();
            if ($resultado) {
                echo "🎉 Limpieza completada:\n";
                echo " - Alertas actualizadas: " . $resultado['actualizadas'] . "\n";
                echo " - Alertas limpiadas de C5: " . $resultado['limpiadas'] . "\n";
            }
            break;
    }
    
    echo "</pre>";
    echo "</div>";
    echo "<hr>";
}

// MOSTRAR ESTADO ACTUAL DE ALERTAS
try {
    $db_privada = new PDO("mysql:host=localhost;dbname=vital_monitor_private", "root", "");
    $db_publica = new PDO("mysql:host=localhost;dbname=vital_monitor_public", "root", "");
    
    echo "<div class='panel'>";
    echo "<h3>📊 Estado Actual de Alertas</h3>";
    
    // Alertas en base PRIVADA
    $alertas_priv = $db_privada->query("
        SELECT id, id_dispositivo, tipo_alerta, estado, sincronizado_c5, 
               DATE_FORMAT(fecha_creacion, '%H:%i %d/%m') as fecha
        FROM log_alertas 
        ORDER BY id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>🔒 Base Privada (log_alertas): " . count($alertas_priv) . " alertas</h4>";
    echo "<table border='1' style='width:100%; border-collapse: collapse; font-size: 14px;'>";
    echo "<tr style='background: #e0e0e0;'><th>ID</th><th>Dispositivo</th><th>Tipo</th><th>Estado</th><th>Sync</th><th>Fecha</th></tr>";
    
    foreach ($alertas_priv as $alerta) {
        $color_estado = [
            'PENDIENTE' => '#ffc107',
            'EN PROCESO' => '#17a2b8', 
            'EN LUGAR' => '#007bff',
            'RESUELTA' => '#28a745',
            'CANCELADA' => '#dc3545'
        ][$alerta['estado']] ?? '#6c757d';
        
        $sync_icon = $alerta['sincronizado_c5'] ? '✅' : '❌';
        
        echo "<tr>";
        echo "<td>{$alerta['id']}</td>";
        echo "<td>{$alerta['id_dispositivo']}</td>";
        echo "<td>{$alerta['tipo_alerta']}</td>";
        echo "<td style='background: {$color_estado}; color: white; text-align: center;'><strong>{$alerta['estado']}</strong></td>";
        echo "<td style='text-align: center;'>{$sync_icon}</td>";
        echo "<td>{$alerta['fecha']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Alertas en base PÚBLICA
    $alertas_pub = $db_publica->query("
        SELECT id, id_alerta_privada, tipo_emergencia, estado,
               DATE_FORMAT(fecha_reporte, '%H:%i %d/%m') as fecha
        FROM alertas_c5 
        ORDER BY id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>🔓 Base Pública C5 (alertas_c5): " . count($alertas_pub) . " alertas</h4>";
    echo "<table border='1' style='width:100%; border-collapse: collapse; font-size: 14px;'>";
    echo "<tr style='background: #e0e0e0;'><th>ID C5</th><th>ID Privada</th><th>Tipo</th><th>Estado</th><th>Fecha</th></tr>";
    
    foreach ($alertas_pub as $alerta) {
        $color_estado = [
            'PENDIENTE' => '#ffc107',
            'EN PROCESO' => '#17a2b8',
            'EN LUGAR' => '#007bff', 
            'RESUELTA' => '#28a745',
            'CANCELADA' => '#dc3545'
        ][$alerta['estado']] ?? '#6c757d';
        
        echo "<tr>";
        echo "<td>{$alerta['id']}</td>";
        echo "<td>{$alerta['id_alerta_privada']}</td>";
        echo "<td>{$alerta['tipo_emergencia']}</td>";
        echo "<td style='background: {$color_estado}; color: white; text-align: center;'><strong>{$alerta['estado']}</strong></td>";
        echo "<td>{$alerta['fecha']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='panel'>";
    echo "<h3>❌ Error cargando estados: " . $e->getMessage() . "</h3>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Sincronización C5 - Con Limpieza</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .panel { background: white; padding: 20px; margin: 15px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        button { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #005a87; }
        input { padding: 8px; margin: 5px; width: 100px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-limpiar { background: #dc3545; }
        .btn-limpiar:hover { background: #c82333; }
    </style>
</head>
<body>

    <div class="panel">
        <h3>🚨 Enviar Alerta a C5</h3>
        <form method="POST">
            <input type="hidden" name="accion" value="enviar">
            <label>ID Alerta Privada:</label>
            <input type="number" name="id" value="1" required>
            <button type="submit">📤 Enviar a C5</button>
        </form>
    </div>

    <div class="panel">
        <h3>🔄 Actualizar desde C5</h3>
        <form method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <label>ID Alerta C5:</label>
            <input type="number" name="id" value="1" required>
            <button type="submit">📥 Traer de C5</button>
        </form>
    </div>

    <div class="panel">
        <h3>⚡ Sincronización Automática</h3>
        <form method="POST">
            <input type="hidden" name="accion" value="batch">
            <button type="submit">🔄 Sincronizar Todo</button>
        </form>
    </div>

    <div class="panel">
        <h3>🧹 Limpiar Alertas Resueltas</h3>
        <form method="POST">
            <input type="hidden" name="accion" value="limpiar">
            <button type="submit" class="btn-limpiar">🗑️ Limpiar Alertas Resueltas de C5</button>
        </form>
        <small>Limpia alertas RESUELTAS/CANCELADAS de C5 y actualiza estados</small>
    </div>

    <div class="panel">
        <h3>📊 Ver Bases de Datos</h3>
        <a href="http://localhost/phpmyadmin" target="_blank">
            <button type="button">📋 Abrir phpMyAdmin</button>
        </a>
    </div>

</body>
</html>