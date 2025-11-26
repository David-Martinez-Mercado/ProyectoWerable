<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$deviceCode = isset($_GET['device']) ? $_GET['device'] : '';

require_once 'config/connection.php';
require_once 'models/DeviceModel.php';

$deviceModel = new DeviceModel();
$device = $deviceCode ? $deviceModel->getDevice($deviceCode, $_SESSION['user_id']) : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoreo en Tiempo Real - Sistema de Monitoreo</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1><i class="fas fa-heartbeat"></i> Monitoreo en Tiempo Real</h1>
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
                <li class="active"><a href="monitoreo_paciente.php"><i class="fas fa-heartbeat"></i> Monitoreo en Tiempo Real</a></li>
                <li><a href="historial_descarga.php"><i class="fas fa-history"></i> Historial</a></li>
                <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="dashboard-main">
            <?php if (!$deviceCode || !$device): ?>
                <!-- Vista cuando no hay dispositivo seleccionado -->
                <div class="no-device-selected">
                    <i class="fas fa-search fa-3x"></i>
                    <h2>Selecciona un dispositivo para monitorear</h2>
                    <p>Ve a "Mis Dispositivos" y elige un paciente para comenzar el monitoreo en tiempo real</p>
                    <a href="dispositivos_lista.php" class="btn-primary">
                        <i class="fas fa-list"></i> Ver Mis Dispositivos
                    </a>
                </div>
            <?php else: ?>
                <!-- Vista de monitoreo con dispositivo seleccionado -->
                <div class="monitoring-header">
                    <div class="patient-info">
                        <h2><i class="fas fa-user"></i> <?php echo htmlspecialchars($device['nombre_paciente']); ?></h2>
                        <p>Dispositivo: <?php echo htmlspecialchars($device['codigo']); ?></p>
                        <p>Edad: <?php echo $device['edad']; ?> años | Enfermedades: <?php echo htmlspecialchars($device['enfermedades_cronicas']); ?></p>
                    </div>
                    <div class="monitoring-actions">
                        <button class="btn-emergency" onclick="triggerMedicalAlert()">
                            <i class="fas fa-ambulance"></i> Emergencia Médica
                        </button>
                        <button class="btn-warning" onclick="triggerMissingAlert()">
                            <i class="fas fa-map-marker-alt"></i> Reportar Extravío
                        </button>
                    </div>
                </div>

                <!-- Alertas en tiempo real -->
                <div id="liveAlerts" class="alerts-live"></div>

                <!-- Grid de Métricas en Tiempo Real -->
                <div class="metrics-grid">
                    <!-- Frecuencia Cardíaca -->
                    <div class="metric-card heart-rate">
                        <div class="metric-header">
                            <h3><i class="fas fa-heart"></i> Frecuencia Cardíaca</h3>
                            <span class="metric-status" id="hrStatus">--</span>
                        </div>
                        <div class="metric-value" id="hrValue">--</div>
                        <div class="metric-unit">lpm</div>
                        <div class="metric-chart">
                            <canvas id="hrChart" height="80"></canvas>
                        </div>
                    </div>

                    <!-- Saturación de Oxígeno -->
                    <div class="metric-card oxygen">
                        <div class="metric-header">
                            <h3><i class="fas fa-lungs"></i> SpO₂</h3>
                            <span class="metric-status" id="spo2Status">--</span>
                        </div>
                        <div class="metric-value" id="spo2Value">--</div>
                        <div class="metric-unit">%</div>
                        <div class="metric-chart">
                            <canvas id="spo2Chart" height="80"></canvas>
                        </div>
                    </div>

                    <!-- Temperatura -->
                    <div class="metric-card temperature">
                        <div class="metric-header">
                            <h3><i class="fas fa-thermometer-half"></i> Temperatura</h3>
                            <span class="metric-status" id="tempStatus">--</span>
                        </div>
                        <div class="metric-value" id="tempValue">--</div>
                        <div class="metric-unit">°C</div>
                        <div class="metric-chart">
                            <canvas id="tempChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Gráficas Detalladas -->
                <div class="charts-section">
                    <h3><i class="fas fa-chart-line"></i> Tendencia de Signos Vitales (Últimas 6 Horas)</h3>
                    <div class="charts-grid">
                        <div class="chart-container">
                            <h4>Frecuencia Cardíaca</h4>
                            <canvas id="detailedHrChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <h4>Saturación de Oxígeno</h4>
                            <canvas id="detailedSpo2Chart"></canvas>
                        </div>
                        <div class="chart-container">
                            <h4>Temperatura Corporal</h4>
                            <canvas id="detailedTempChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Mapa de Ubicación -->
                <div class="map-section">
                    <h3><i class="fas fa-map-marked-alt"></i> Ubicación en Tiempo Real</h3>
                    <div class="map-container">
                        <div id="patientMap" style="height: 400px; border-radius: 10px;"></div>
                        <div class="map-info">
                            <p><strong>Última actualización:</strong> <span id="lastLocationUpdate">--</span></p>
                            <p><strong>Coordenadas:</strong> <span id="currentCoordinates">--</span></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Script de Google Maps -->
    <script src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY_GOOGLE_MAPS&callback=initMap" async defer></script>
    
    <!-- Scripts de monitoreo -->
    <script>
        const currentDevice = '<?php echo $deviceCode; ?>';
        const apiBaseUrl = 'api/readings.php';
    </script>
    <script src="assets/monitoring.js"></script>
</body>
</html>