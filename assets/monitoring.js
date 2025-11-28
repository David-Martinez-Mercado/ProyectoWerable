// ==== FUNCIÓN showAlert ====
if (typeof showAlert === 'undefined') {
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            z-index: 10000;
            max-width: 300px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            font-family: Arial, sans-serif;
            ${type === 'success' ? 'background: #28a745;' : ''}
            ${type === 'error' ? 'background: #dc3545;' : ''}
            ${type === 'warning' ? 'background: #ffc107; color: black;' : ''}
        `;
        alertDiv.innerHTML = `
            <strong>${type === 'success' ? '✅' : type === 'error' ? '❌' : '⚠️'}</strong>
            ${message}
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

// Configuración global
const config = {
    updateInterval: 10000, // 10 segundos (más tiempo para evitar sobrecarga)
    apiBaseUrl: 'http://localhost/proyecto/api/readings.php'
};

let charts = {};
let updateInterval;

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏥 Inicializando monitoreo...');
    
    if (typeof Chart === 'undefined') {
        console.error('❌ Chart.js no está cargado');
        showAlert('Error: Chart.js no está cargado', 'error');
        return;
    }
    
    initializeAllCharts(); // Una sola función para todas las gráficas
    initializeSimpleMap();
    startRealTimeUpdates();
});

// Inicializar TODAS las gráficas de una vez
function initializeAllCharts() {
    console.log('📊 Inicializando todas las gráficas...');
    
    // === GRÁFICAS MINI (tarjetas) ===
    const miniConfig = {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            elements: { point: { radius: 0 } }
        }
    };

    // Gráfica mini FC
    const hrCtx = document.getElementById('hrChart');
    if (hrCtx) {
        charts.hrMini = new Chart(hrCtx, {
            ...miniConfig,
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    data: Array(10).fill(75),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            }
        });
    }

    // Gráfica mini SpO2
    const spo2Ctx = document.getElementById('spo2Chart');
    if (spo2Ctx) {
        charts.spo2Mini = new Chart(spo2Ctx, {
            ...miniConfig,
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    data: Array(10).fill(98),
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            }
        });
    }

    // Gráfica mini Temperatura
    const tempCtx = document.getElementById('tempChart');
    if (tempCtx) {
        charts.tempMini = new Chart(tempCtx, {
            ...miniConfig,
            data: {
                labels: Array(10).fill(''),
                datasets: [{
                    data: Array(10).fill(36.8),
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            }
        });
    }

    // === GRÁFICAS DETALLADAS (6 horas) ===
    const detailedConfig = {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: true, title: { display: true, text: 'Hora' } },
                y: { display: true, title: { display: true, text: 'Valor' } }
            }
        }
    };

    // Datos demo para 6 horas
    const timeLabels = ['06:00', '07:00', '08:00', '09:00', '10:00', '11:00'];
    const hrData = [72, 74, 76, 78, 75, 73];
    const spo2Data = [97, 98, 97, 98, 99, 98];
    const tempData = [36.5, 36.6, 36.7, 36.8, 36.7, 36.6];

    // Gráfica detallada FC
    const detailedHrCtx = document.getElementById('detailedHrChart');
    if (detailedHrCtx) {
        charts.hrDetailed = new Chart(detailedHrCtx, {
            ...detailedConfig,
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'FC (lpm)',
                    data: hrData,
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            }
        });
        console.log('✅ Gráfica FC detallada inicializada');
    }

    // Gráfica detallada SpO2
    const detailedSpo2Ctx = document.getElementById('detailedSpo2Chart');
    if (detailedSpo2Ctx) {
        charts.spo2Detailed = new Chart(detailedSpo2Ctx, {
            ...detailedConfig,
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'SpO2 (%)',
                    data: spo2Data,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            }
        });
        console.log('✅ Gráfica SpO2 detallada inicializada');
    }

    // Gráfica detallada Temperatura
    const detailedTempCtx = document.getElementById('detailedTempChart');
    if (detailedTempCtx) {
        charts.tempDetailed = new Chart(detailedTempCtx, {
            ...detailedConfig,
            data: {
                labels: timeLabels,
                datasets: [{
                    label: 'Temp (°C)',
                    data: tempData,
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            }
        });
        console.log('✅ Gráfica Temperatura detallada inicializada');
    }
}

// Mapa simple
function initializeSimpleMap() {
    console.log('🗺️ Inicializando mapa simple...');
    const mapContainer = document.getElementById('patientMap');
    if (mapContainer) {
        mapContainer.innerHTML = `
            <div style="width:100%; height:100%; background:#f8f9fa; display:flex; align-items:center; justify-content:center; border-radius:10px;">
                <div style="text-align:center; color:#666;">
                    <i class="fas fa-map-marker-alt" style="font-size:48px; margin-bottom:10px; color:#e74c3c;"></i>
                    <p><strong>Mapa de Ubicación</strong></p>
                    <p>Coordenadas: <span id="mapCoordinates">Cargando...</span></p>
                    <p><small>Última actualización: <span id="mapLastUpdate">--</span></small></p>
                </div>
            </div>
        `;
    }
}

// Iniciar actualizaciones
function startRealTimeUpdates() {
    console.log('🔄 Iniciando actualizaciones...');
    updateAllData(); // Actualizar inmediatamente
    updateInterval = setInterval(updateAllData, config.updateInterval);
}

// Actualizar todos los datos (UNA SOLA PETICIÓN)
async function updateAllData() {
    try {
        console.log('📡 Solicitando datos...');
        const response = await fetch(`${config.apiBaseUrl}?device=ESP32-001&limit=1`);
        
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const data = await response.json();
        console.log('📈 Datos recibidos:', data);
        
        if (data && data.length > 0) {
            const reading = data[0];
            updateUI(reading); // Actualizar toda la UI de una vez
        } else {
            useDemoData(); // Usar datos demo si no hay datos reales
        }
    } catch (error) {
        console.error('❌ Error:', error);
        useDemoData(); // Usar datos demo en caso de error
    }
}

// Actualizar toda la interfaz de una vez
function updateUI(reading) {
    // Actualizar valores principales
    updateMetricValue('hrValue', reading.lectura_FC);
    updateMetricValue('spo2Value', reading.lectura_SpO2);
    updateMetricValue('tempValue', reading.lectura_temperatura);
    
    // Actualizar estados
    updateMetricStatus('hrStatus', reading.lectura_FC, 60, 100);
    updateMetricStatus('spo2Status', reading.lectura_SpO2, 90, 100);
    updateMetricStatus('tempStatus', reading.lectura_temperatura, 35.5, 37.5);
    
    // Actualizar ubicación
    if (reading.gps_lat && reading.gps_lon) {
        updateLocationInfo(reading.gps_lat, reading.gps_lon, reading.fecha_lectura);
    }
    
    // Actualizar gráficas mini
    updateMiniChart(charts.hrMini, reading.lectura_FC);
    updateMiniChart(charts.spo2Mini, reading.lectura_SpO2);
    updateMiniChart(charts.tempMini, reading.lectura_temperatura);
}

// Usar datos de demostración
function useDemoData() {
    console.log('🎭 Usando datos demo...');
    const demoData = {
        lectura_FC: 78,
        lectura_SpO2: 98,
        lectura_temperatura: 36.8,
        gps_lat: 19.432607,
        gps_lon: -99.133208,
        fecha_lectura: new Date().toISOString()
    };
    updateUI(demoData);
}

// Funciones auxiliares (mantener igual)
function updateMetricValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element && value !== null) {
        element.textContent = value;
        element.classList.add('value-updated');
        setTimeout(() => element.classList.remove('value-updated'), 1000);
    }
}

function updateMetricStatus(elementId, value, min, max) {
    const element = document.getElementById(elementId);
    if (element && value !== null) {
        let status = 'normal', statusText = 'Normal', icon = '✅';
        if (value < min || value > max) {
            status = 'critical'; statusText = 'Crítico'; icon = '🚨';
        } else if (value < min * 1.1 || value > max * 0.9) {
            status = 'warning'; statusText = 'Alerta'; icon = '⚠️';
        }
        element.innerHTML = `${icon} ${statusText}`;
        element.className = `metric-status ${status}`;
    }
}

function updateLocationInfo(lat, lon, timestamp) {
    const coords = `${parseFloat(lat).toFixed(6)}, ${parseFloat(lon).toFixed(6)}`;
    const time = new Date(timestamp).toLocaleString();
    
    document.getElementById('currentCoordinates').textContent = coords;
    document.getElementById('lastLocationUpdate').textContent = time;
    document.getElementById('mapCoordinates').textContent = coords;
    document.getElementById('mapLastUpdate').textContent = time;
}

function updateMiniChart(chart, newValue) {
    if (!chart || newValue === null) return;
    chart.data.labels.push('');
    chart.data.datasets[0].data.push(parseFloat(newValue));
    if (chart.data.labels.length > 10) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }
    chart.update('none');
}

// Funciones de alertas (mantener igual)
async function triggerMedicalAlert() {
    if (!confirm('¿Activar alerta médica?')) return;
    try {
        const response = await fetch('http://localhost/proyecto/api/alerts_simple.php', {
            method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=medical&device=ESP32-001'
        });
        const data = await response.json();
        if (data.success) {
            showAlert(data.message, data.sync_status === 'success' ? 'success' : 'warning');
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error de conexión', 'error');
    }
}

async function triggerMissingAlert() {
    if (!confirm('¿Reportar extravío?')) return;
    try {
        const response = await fetch('http://localhost/proyecto/api/alerts_simple.php', {
            method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=extravio&device=ESP32-001'
        });
        const data = await response.json();
        if (data.success) {
            showAlert(data.message, data.sync_status === 'success' ? 'success' : 'warning');
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error de conexión', 'error');
    }
}

window.triggerMedicalAlert = triggerMedicalAlert;
window.triggerMissingAlert = triggerMissingAlert;

window.addEventListener('beforeunload', () => {
    if (updateInterval) clearInterval(updateInterval);
});

console.log('✅ monitoring.js cargado - Versión RÁPIDA');