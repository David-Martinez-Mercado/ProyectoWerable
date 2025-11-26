<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'config/connection.php';
require_once 'models/DeviceModel.php';
require_once 'models/AlertModel.php';

$deviceModel = new DeviceModel();
$alertModel = new AlertModel();

$devices = $deviceModel->getUserDevices($_SESSION['user_id']);
$activeAlerts = $alertModel->getActiveAlerts($_SESSION['user_id']);

// Actualizar estado de conexión de dispositivos
foreach ($devices as &$device) {
    $isConnected = $deviceModel->checkDeviceConnection($device['codigo']);
    $newStatus = $isConnected ? 'online' : 'offline';
    
    // Solo actualizar si cambió el estado
    if ($device['estado'] !== $newStatus) {
        $deviceModel->updateDeviceStatus($device['codigo'], $newStatus);
        $device['estado'] = $newStatus;
    }
}
unset($device);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Dispositivos - Sistema de Monitoreo</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1><i class="fas fa-heartbeat"></i> Monitoreo de Signos Vitales</h1>
                <div class="user-menu">
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span>Bienvenido, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="api/auth.php?action=logout" class="btn-logout">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="dashboard-nav">
            <ul>
                <li class="active"><a href="dispositivos_lista.php"><i class="fas fa-list"></i> Mis Dispositivos</a></li>
                <li><a href="monitoreo_paciente.php"><i class="fas fa-heartbeat"></i> Monitoreo en Tiempo Real</a></li>
                <li><a href="historial_descarga.php"><i class="fas fa-history"></i> Historial</a></li>
                <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="page-header">
                <h2><i class="fas fa-microchip"></i> Mis Dispositivos Asociados</h2>
                <p>Gestiona y monitorea los dispositivos de tus pacientes</p>
            </div>

            <!-- Alertas Activas -->
            <?php if (!empty($activeAlerts)): ?>
            <div class="alerts-section">
                <h3><i class="fas fa-bell"></i> Alertas Activas</h3>
                <div class="alerts-container">
                    <?php foreach ($activeAlerts as $alert): ?>
                    <div class="alert-item <?php echo $alert['tipo_alerta'] === 'medica' ? 'critical' : 'warning'; ?>">
                        <div class="alert-header">
                            <strong>
                                <?php echo $alert['tipo_alerta'] === 'medica' ? '🚨 Emergencia Médica' : '⚠️ Paciente Extraviado'; ?>
                            </strong>
                            <span class="alert-status"><?php echo $alert['estado']; ?></span>
                        </div>
                        <p>Paciente: <?php echo htmlspecialchars($alert['nombre_paciente']); ?></p>
                        <p>Dispositivo: <?php echo htmlspecialchars($alert['id_dispositivo']); ?></p>
                        <small>Iniciada: <?php echo date('d/m/Y H:i', strtotime($alert['fecha_creacion'])); ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Grid de Dispositivos -->
            <div class="devices-grid">
                <?php if (empty($devices)): ?>
                    <div class="no-devices">
                        <i class="fas fa-microchip-slash fa-3x"></i>
                        <h3>No hay dispositivos registrados</h3>
                        <p>Agrega tu primer dispositivo para comenzar el monitoreo</p>
                        <button class="btn-primary" onclick="showAddDeviceModal()">
                            <i class="fas fa-plus"></i> Agregar Dispositivo
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($devices as $device): ?>
                        <div class="device-card" data-device-id="<?php echo $device['codigo']; ?>">
                            <div class="device-header">
                                <h3><?php echo htmlspecialchars($device['nombre_paciente']); ?></h3>
                                <span class="device-status <?php echo $device['estado']; ?>">
                                    <i class="fas fa-circle"></i>
                                    <?php echo $device['estado'] === 'online' ? 'Conectado' : 'Desconectado'; ?>
                                </span>
                            </div>
                            
                            <div class="device-info">
                                <p><strong>ID:</strong> <?php echo htmlspecialchars($device['codigo']); ?></p>
                                <p><strong>Paciente:</strong> <?php echo htmlspecialchars($device['nombre_paciente']); ?></p>
                                <p><strong>Edad:</strong> <?php echo $device['edad']; ?> años</p>
                                <p><strong>Contacto Emergencia:</strong> <?php echo htmlspecialchars($device['contacto_emergencia']); ?></p>
                                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($device['direccion_residencia']); ?></p>
                                <p><strong>Enfermedades:</strong> <?php echo htmlspecialchars($device['enfermedades_cronicas']); ?></p>
                                
                                <!-- Umbrales Personalizados -->
                                <div class="thresholds-info">
                                    <small><strong>Umbrales de Alerta:</strong> 
                                        FC: <?php echo $device['umbral_FC_min'] ?? 60; ?>-<?php echo $device['umbral_FC_max'] ?? 100; ?> lpm | 
                                        SpO₂: ><?php echo $device['umbral_SpO2_min'] ?? 90; ?>% | 
                                        Temp: <?php echo $device['umbral_temperatura_min'] ?? 35.5; ?>-<?php echo $device['umbral_temperatura_max'] ?? 37.5; ?>°C
                                    </small>
                                </div>
                                
                                <?php if ($device['lectura_FC']): ?>
                                <div class="vital-signs-mini">
                                    <div class="vital-sign">
                                        <span class="vital-label">FC:</span>
                                        <span class="vital-value"><?php echo $device['lectura_FC']; ?> lpm</span>
                                    </div>
                                    <div class="vital-sign">
                                        <span class="vital-label">SpO₂:</span>
                                        <span class="vital-value"><?php echo $device['lectura_SpO2']; ?>%</span>
                                    </div>
                                    <div class="vital-sign">
                                        <span class="vital-label">Temp:</span>
                                        <span class="vital-value"><?php echo $device['lectura_temperatura']; ?>°C</span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="device-actions">
                                <a href="monitoreo_paciente.php?device=<?php echo urlencode($device['codigo']); ?>" class="btn-primary">
                                    <i class="fas fa-chart-line"></i> Monitorear
                                </a>
                                <button class="btn-secondary" onclick="showDeviceConfig('<?php echo $device['codigo']; ?>')">
                                    <i class="fas fa-cog"></i> Umbrales
                                </button>
                            </div>

                            <?php if ($device['ultima_lectura']): ?>
                                <div class="device-footer">
                                    <small>Última actualización: <?php echo date('H:i', strtotime($device['ultima_lectura'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Botón Flotante para agregar dispositivo -->
            <?php if (!empty($devices)): ?>
                <button class="floating-btn" onclick="showAddDeviceModal()">
                    <i class="fas fa-plus"></i>
                </button>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal para agregar dispositivo -->
    <div id="addDeviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Agregar Nuevo Dispositivo</h3>
                <span class="close" onclick="closeAddDeviceModal()">&times;</span>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <form id="addDeviceForm">
                    <div class="form-group">
                        <label for="deviceCode">Código del Dispositivo:</label>
                        <input type="text" id="deviceCode" name="deviceCode" required 
                               placeholder="Ej: ESP32-001">
                    </div>
                    
                    <div class="form-group">
                        <label for="patientName">Nombre del Paciente:</label>
                        <input type="text" id="patientName" name="patientName" required 
                               placeholder="Nombre completo del paciente">
                    </div>
                    
                    <div class="form-group">
                        <label for="birthDate">Fecha de Nacimiento:</label>
                        <input type="date" id="birthDate" name="birthDate" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="age">Edad:</label>
                        <input type="number" id="age" name="age" required 
                               min="1" max="120" placeholder="Edad del paciente">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Dirección de Residencia:</label>
                        <textarea id="address" name="address" required 
                                  placeholder="Dirección completa de residencia..." rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="collectionAddress">Dirección de Recolección (Opcional):</label>
                        <textarea id="collectionAddress" name="collectionAddress" 
                                  placeholder="Dirección para recolección en emergencias..." rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="emergencyContact">Contacto de Emergencia:</label>
                        <input type="text" id="emergencyContact" name="emergencyContact" required 
                               placeholder="Nombre y teléfono de contacto">
                    </div>
                    
                    <div class="form-group">
                        <label for="patientConditions">Enfermedades Crónicas:</label>
                        <textarea id="patientConditions" name="patientConditions" 
                                  placeholder="Descripción de enfermedades crónicas..." rows="3"></textarea>
                    </div>
                    
                    <!-- Umbrales Personalizados -->
                    <div class="form-group">
                        <h4>Umbrales de Alerta Personalizados</h4>
                        <p class="form-help">Configure los valores mínimos y máximos para generar alertas automáticas</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="heartRateMin">Frecuencia Cardíaca Mínima (lpm):</label>
                        <input type="number" id="heartRateMin" name="heartRateMin" value="60" min="40" max="200" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="heartRateMax">Frecuencia Cardíaca Máxima (lpm):</label>
                        <input type="number" id="heartRateMax" name="heartRateMax" value="100" min="40" max="200" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="spO2Min">Saturación de Oxígeno Mínima (%):</label>
                        <input type="number" id="spO2Min" name="spO2Min" value="90" min="70" max="100" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tempMin">Temperatura Mínima (°C):</label>
                        <input type="number" step="0.1" id="tempMin" name="tempMin" value="35.5" min="30" max="45" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tempMax">Temperatura Máxima (°C):</label>
                        <input type="number" step="0.1" id="tempMax" name="tempMax" value="37.5" min="30" max="45" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAddDeviceModal()">Cancelar</button>
                        <button type="submit" class="btn-primary">Agregar Dispositivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para configurar umbrales -->
    <div id="configDeviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Configurar Umbrales de Alerta</h3>
                <span class="close" onclick="closeConfigDeviceModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div id="configDeviceContent">
                    <!-- Contenido cargado dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tema oscuro
        function toggleTheme() {
            document.body.classList.toggle('dark-theme');
            const isDark = document.body.classList.contains('dark-theme');
            localStorage.setItem('darkTheme', isDark);
            
            // Cambiar icono
            const icon = document.querySelector('.theme-toggle i');
            if (isDark) {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }

        // Cargar tema al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('darkTheme');
            if (savedTheme === 'true') {
                document.body.classList.add('dark-theme');
                const icon = document.querySelector('.theme-toggle i');
                if (icon) icon.className = 'fas fa-sun';
            }
        });

        // Modales
        function showAddDeviceModal() {
            document.getElementById('addDeviceModal').style.display = 'block';
        }

        function closeAddDeviceModal() {
            document.getElementById('addDeviceModal').style.display = 'none';
        }

        function showDeviceConfig(deviceCode) {
            fetch(`api/devices.php?id=${deviceCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showConfigModal(data.device);
                    } else {
                        alert('Error al cargar la configuración del dispositivo');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión al cargar la configuración');
                });
        }

        function showConfigModal(device) {
            const content = `
                <form id="configDeviceForm">
                    <div class="form-group">
                        <h4>Umbrales de Alerta para ${device.nombre_paciente}</h4>
                        <p class="form-help">Configure los valores para generar alertas automáticas</p>
                    </div>
                    
                    <div class="form-group">
                        <label>Frecuencia Cardíaca (lpm):</label>
                        <div class="range-inputs">
                            <input type="number" name="heart_rate_min" value="${device.umbral_FC_min || 60}" min="40" max="200" placeholder="Mínima" required>
                            <span>a</span>
                            <input type="number" name="heart_rate_max" value="${device.umbral_FC_max || 100}" min="40" max="200" placeholder="Máxima" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="spO2_min">Saturación de Oxígeno Mínima (%):</label>
                        <input type="number" id="spO2_min" name="spO2_min" value="${device.umbral_SpO2_min || 90}" min="70" max="100" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Temperatura (°C):</label>
                        <div class="range-inputs">
                            <input type="number" step="0.1" name="temp_min" value="${device.umbral_temperatura_min || 35.5}" min="30" max="45" placeholder="Mínima" required>
                            <span>a</span>
                            <input type="number" step="0.1" name="temp_max" value="${device.umbral_temperatura_max || 37.5}" min="30" max="45" placeholder="Máxima" required>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeConfigDeviceModal()">Cancelar</button>
                        <button type="submit" class="btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            `;
            
            document.getElementById('configDeviceContent').innerHTML = content;
            document.getElementById('configDeviceModal').style.display = 'block';
            
            // Agregar evento al formulario
            document.getElementById('configDeviceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                updateDeviceConfig(device.codigo);
            });
        }

        function closeConfigDeviceModal() {
            document.getElementById('configDeviceModal').style.display = 'none';
        }

        function updateDeviceConfig(deviceCode) {
            const form = document.getElementById('configDeviceForm');
            const formData = new FormData(form);
            const thresholds = {
                heart_rate_min: formData.get('heart_rate_min'),
                heart_rate_max: formData.get('heart_rate_max'),
                spO2_min: formData.get('spO2_min'),
                temp_min: formData.get('temp_min'),
                temp_max: formData.get('temp_max')
            };

            fetch(`api/devices.php?id=${deviceCode}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ thresholds: thresholds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Umbrales actualizados correctamente');
                    closeConfigDeviceModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar los umbrales');
            });
        }

        // Cerrar modales al hacer click fuera
        window.onclick = function(event) {
            const addModal = document.getElementById('addDeviceModal');
            const configModal = document.getElementById('configDeviceModal');
            
            if (event.target === addModal) closeAddDeviceModal();
            if (event.target === configModal) closeConfigDeviceModal();
        }

        // Calcular edad automáticamente desde fecha de nacimiento
        document.getElementById('birthDate').addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            document.getElementById('age').value = age;
        });

        // Manejar envío del formulario de agregar dispositivo
        document.getElementById('addDeviceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                deviceCode: formData.get('deviceCode'),
                patientName: formData.get('patientName'),
                birthDate: formData.get('birthDate'),
                age: formData.get('age'),
                address: formData.get('address'),
                collectionAddress: formData.get('collectionAddress'),
                emergencyContact: formData.get('emergencyContact'),
                conditions: formData.get('patientConditions'),
                thresholds: {
                    heart_rate_min: formData.get('heartRateMin'),
                    heart_rate_max: formData.get('heartRateMax'),
                    spO2_min: formData.get('spO2Min'),
                    temp_min: formData.get('tempMin'),
                    temp_max: formData.get('tempMax')
                }
            };
            
            fetch('api/devices.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Dispositivo agregado exitosamente');
                    closeAddDeviceModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al agregar el dispositivo');
            });
        });
    </script>
</body>
</html>