<?php
// config.php - Configuración para XAMPP
class Config {
    // Base de datos PRIVADA
    const DB_PRIVATE = [
        'host' => 'localhost',
        'dbname' => 'vital_monitor_private',
        'username' => 'root',
        'password' => ''
    ];
    
    // Base de datos PÚBLICA (C5)
    const DB_PUBLIC = [
        'host' => 'localhost',
        'dbname' => 'vital_monitor_public', 
        'username' => 'root',
        'password' => ''
    ];
}
?>