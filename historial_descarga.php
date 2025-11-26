<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/connection.php';
require_once 'models/DeviceModel.php';
require_once 'models/LecturaModel.php';

$deviceModel = new DeviceModel();
$lecturaModel = new LecturaModel();

$devices = $deviceModel->getUserDevices($_SESSION['user_id']);
$selectedDevice = isset($_GET['device']) ? $_GET['device'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$historicalData = [];
if ($selectedDevice) {
    $historicalData = $lecturaModel->getHistoricalData($selectedDevice, $startDate, $endDate);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial y Descargas - Sistema de Monitoreo</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1><i class="fas fa-heartbeat"></i> Historial y Descargas</h1>
                <div class="user-menu">
                    <span>Bienvenido, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="api/auth.php?action=logout" class="btn-logout">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="dashboard-nav">
            <ul>
                <li><a href="dispositivos_lista.php"><i class="fas fa-list"></i> Mis Dispositivos</a></li>
                <li><a href="monitoreo_paciente.php"><i class="fas fa-heartbeat"></i> Monitoreo en Tiempo Real</a></li>
                <li class="active"><a href="historial_descarga.php"><i class="fas fa-history"></i> Historial</a></li>
                <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="page-header">
                <h2><i class="fas fa-history"></i> Historial de Signos Vitales</h2>
                <p>Consulta y descarga el historial médico de tus pacientes</p>
            </div>

            <!-- Filtros -->
            <div class="filters-section">
                <form method="GET" class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="device">Dispositivo:</label>
                            <select id="device" name="device" required>
                                <option value="">Selecciona un dispositivo</option>
                                <?php foreach ($devices as $device): ?>
                                    <option value="<?php echo $device['codigo']; ?>" 
                                        <?php echo $selectedDevice == $device['codigo'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($device['nombre_paciente']); ?> (<?php echo $device['codigo']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="start_date">Fecha Inicio:</label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo $startDate; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">Fecha Fin:</label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo $endDate; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            <?php if ($selectedDevice && !empty($historicalData)): ?>
                                <button type="button" class="btn-secondary" onclick="exportToCSV()">
                                    <i class="fas fa-download"></i> Exportar CSV
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Resultados -->
            <?php if ($selectedDevice): ?>
                <div class="results-section">
                    <h3>
                        <i class="fas fa-chart-bar"></i> 
                        Datos Históricos 
                        <?php if (!empty($historicalData)): ?>
                            <span class="result-count">(<?php echo count($historicalData); ?> registros)</span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if (empty($historicalData)): ?>
                        <div class="no-data">
                            <i class="fas fa-database fa-2x"></i>
                            <p>No se encontraron registros para el período seleccionado</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Fecha y Hora</th>
                                        <th>FC (lpm)</th>
                                        <th>SpO₂ (%)</th>
                                        <th>Temp (°C)</th>
                                        <th>Ubicación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historicalData as $reading): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($reading['fecha_lectura'])); ?></td>
                                            <td class="<?php echo getHeartRateClass($reading['lectura_FC']); ?>">
                                                <?php echo $reading['lectura_FC']; ?>
                                            </td>
                                            <td class="<?php echo getSpO2Class($reading['lectura_SpO2']); ?>">
                                                <?php echo $reading['lectura_SpO2']; ?>
                                            </td>
                                            <td class="<?php echo getTemperatureClass($reading['lectura_temperatura']); ?>">
                                                <?php echo $reading['lectura_temperatura']; ?>
                                            </td>
                                            <td>
                                                <?php if ($reading['gps_lat'] && $reading['gps_lon']): ?>
                                                    <a href="https://maps.google.com/?q=<?php echo $reading['gps_lat']; ?>,<?php echo $reading['gps_lon']; ?>" 
                                                       target="_blank" class="location-link">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        Ver en mapa
                                                    </a>
                                                <?php else: ?>
                                                    <span class="no-location">No disponible</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Resumen Estadístico -->
                        <div class="stats-summary">
                            <h4><i class="fas fa-chart-pie"></i> Resumen Estadístico</h4>
                            <div class="stats-grid">
                                <div class="stat-card">
                                    <h5>Frecuencia Cardíaca</h5>
                                    <div class="stat-values">
                                        <span>Promedio: <?php echo calculateAverage($historicalData, 'lectura_FC'); ?> lpm</span>
                                        <span>Máx: <?php echo calculateMax($historicalData, 'lectura_FC'); ?> lpm</span>
                                        <span>Mín: <?php echo calculateMin($historicalData, 'lectura_FC'); ?> lpm</span>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <h5>Saturación de Oxígeno</h5>
                                    <div class="stat-values">
                                        <span>Promedio: <?php echo calculateAverage($historicalData, 'lectura_SpO2'); ?>%</span>
                                        <span>Máx: <?php echo calculateMax($historicalData, 'lectura_SpO2'); ?>%</span>
                                        <span>Mín: <?php echo calculateMin($historicalData, 'lectura_SpO2'); ?>%</span>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <h5>Temperatura</h5>
                                    <div class="stat-values">
                                        <span>Promedio: <?php echo calculateAverage($historicalData, 'lectura_temperatura'); ?>°C</span>
                                        <span>Máx: <?php echo calculateMax($historicalData, 'lectura_temperatura'); ?>°C</span>
                                        <span>Mín: <?php echo calculateMin($historicalData, 'lectura_temperatura'); ?>°C</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function exportToCSV() {
            const device = '<?php echo $selectedDevice; ?>';
            const startDate = '<?php echo $startDate; ?>';
            const endDate = '<?php echo $endDate; ?>';
            
            window.location.href = `api/readings.php?device=${device}&export=csv&start_date=${startDate}&end_date=${endDate}`;
        }
    </script>
</body>
</html>

<?php
// Funciones auxiliares para cálculos
function calculateAverage($data, $field) {
    $sum = 0;
    $count = 0;
    foreach ($data as $reading) {
        if ($reading[$field] !== null) {
            $sum += $reading[$field];
            $count++;
        }
    }
    return $count > 0 ? round($sum / $count, 1) : 0;
}

function calculateMax($data, $field) {
    $max = null;
    foreach ($data as $reading) {
        if ($reading[$field] !== null && ($max === null || $reading[$field] > $max)) {
            $max = $reading[$field];
        }
    }
    return $max !== null ? $max : 0;
}

function calculateMin($data, $field) {
    $min = null;
    foreach ($data as $reading) {
        if ($reading[$field] !== null && ($min === null || $reading[$field] < $min)) {
            $min = $reading[$field];
        }
    }
    return $min !== null ? $min : 0;
}

// Funciones para clases CSS según valores
function getHeartRateClass($value) {
    if ($value < 60 || $value > 100) return 'critical-value';
    if ($value < 65 || $value > 95) return 'warning-value';
    return 'normal-value';
}

function getSpO2Class($value) {
    if ($value < 90) return 'critical-value';
    if ($value < 95) return 'warning-value';
    return 'normal-value';
}

function getTemperatureClass($value) {
    if ($value < 35.5 || $value > 37.5) return 'critical-value';
    if ($value < 36 || $value > 37.2) return 'warning-value';
    return 'normal-value';
}
?>