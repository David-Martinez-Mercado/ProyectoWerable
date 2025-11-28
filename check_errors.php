<?php
// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Últimos errores del log:</h2>";
$logFile = 'C:\xampp\php\logs\php_error_log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20); // Últimas 20 líneas
    echo "<pre>" . implode("", $lastLines) . "</pre>";
} else {
    echo "Log file not found: " . $logFile;
}
?>