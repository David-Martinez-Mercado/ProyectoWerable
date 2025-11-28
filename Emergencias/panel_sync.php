<?php
// panel_sync.php - Panel visual para sincronización
echo "<h2>🔧 Sistema de Sincronización C5</h2>";

if (isset($_POST['accion'])) {
    require_once 'sync_system/sync_c5_api.php';  // ← RUTA ACTUALIZADA
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
    }
    
    echo "</pre>";
    echo "</div>";
    echo "<hr>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sistema de Sincronización C5</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .panel { background: white; padding: 20px; margin: 15px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        button { padding: 10px 15px; margin: 5px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #005a87; }
        input { padding: 8px; margin: 5px; width: 100px; border: 1px solid #ddd; border-radius: 4px; }
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
        <small>ID de la alerta en log_alertas (base privada)</small>
    </div>

    <div class="panel">
        <h3>🔄 Actualizar desde C5</h3>
        <form method="POST">
            <input type="hidden" name="accion" value="actualizar">
            <label>ID Alerta C5:</label>
            <input type="number" name="id" value="1" required>
            <button type="submit">📥 Traer de C5</button>
        </form>
        <small>ID de la alerta en alertas_c5 (base pública)</small>
    </div>

    <div class="panel">
        <h3>⚡ Sincronización Automática</h3>
        <form method="POST">
            <input type="hidden" name="accion" value="batch">
            <button type="submit">🔄 Sincronizar Todo Automáticamente</button>
        </form>
        <small>Busca alertas pendientes y actualiza estados</small>
    </div>

    <div class="panel">
        <h3>📊 Ver Bases de Datos</h3>
        <a href="http://localhost/phpmyadmin" target="_blank">
            <button type="button">📋 Abrir phpMyAdmin</button>
        </a>
    </div>

</body>
</html>