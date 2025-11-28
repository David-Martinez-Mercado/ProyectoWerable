<?php
// sync_handler.php - Manejar desde línea de comandos o web
require_once 'sync_c5_api.php';

// Permitir acceso web y CLI
if (php_sapi_name() === 'cli') {
    $accion = $argv[1] ?? '';
    $id = $argv[2] ?? 0;
} else {
    $accion = $_GET['accion'] ?? '';
    $id = $_GET['id'] ?? 0;
    
    // Seguridad básica para acceso web
    $token = $_GET['token'] ?? '';
    if ($token !== 'TU_TOKEN_SECRETO') {  // Cambia esto
        die('No autorizado');
    }
}

$sincronizador = new SincronizadorC5();

switch ($accion) {
    case 'enviar':
        if ($id) {
            $sincronizador->enviarAlertaC5($id);
        } else {
            echo "❌ Se requiere ID de alerta\n";
        }
        break;
        
    case 'actualizar':
        if ($id) {
            $sincronizador->actualizarEstadoDesdeC5($id);
        } else {
            echo "❌ Se requiere ID de alerta C5\n";
        }
        break;
        
    case 'batch':
        $sincronizador->sincronizarPendientes();
        break;
        
    default:
        echo "Uso:\n";
        echo "  php sync_handler.php enviar [ID_ALERTA_PRIVADA]\n";
        echo "  php sync_handler.php actualizar [ID_ALERTA_C5]\n"; 
        echo "  php sync_handler.php batch\n";
        echo "  O por web: ?accion=enviar&id=123&token=TU_TOKEN_SECRETO\n";
}
?>