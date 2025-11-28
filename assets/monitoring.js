// ==== FUNCIÓN showAlert AGREGADA ====
if (typeof showAlert === 'undefined') {
    function showAlert(message, type) {
        // Crear elemento de alerta bonito
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
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.remove();
            }
        }, 5000);
    }
}
// ==== FIN DE showAlert ====

// Configuración global
const config = {
    updateInterval: 5000, // 5 segundos
    chartHistory: 6 // horas
};

// Variables globales
let charts = {};
let patientMap = null;
let mapMarker = null;
let updateInterval;

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    if (!currentDevice) return;
    
    initializeCharts();
    initializeMap();
    startRealTimeUpdates();
    
    // Cargar datos iniciales
    updateAllData();
});

// Inicializar gráficas
function initializeCharts() {
    // Gráficas mini en tarjetas
    const miniChartConfig = {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            },
            scales: {
                x: { display: false },
                y: { display: false }
            },
            elements: {
                point: { radius: 0 }
            }
        }
    };
    
    // Gráficas detalladas
    const detailedChartConfig = {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Valor',
                data: [],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    display: true,
                    title: { display: true, text: 'Hora' }
                },
                y: {
                    display: true,
                    title: { display: true, text: 'Valor' }
                }
            }
        }
    };
    
    // Inicializar gráficas mini
    charts.hrMini = new Chart(document.getElementById('hrChart'), {
        ...miniChartConfig,
        data: {
            ...miniChartConfig.data,
            datasets: [{
                ...miniChartConfig.data.datasets[0],
                borderColor: '#e74c3c'
            }]
        }
    });
    
    charts.spo2Mini = new Chart(document.getElementById('spo2Chart'), {
        ...miniChartConfig,
        data: {
            ...miniChartConfig.data,
            datasets: [{
                ...miniChartConfig.data.datasets[0],
                borderColor: '#3498db'
            }]
        }
    });
    
    charts.tempMini = new Chart(document.getElementById('tempChart'), {
        ...miniChartConfig,
        data: {
            ...miniChartConfig.data,
            datasets: [{
                ...miniChartConfig.data.datasets[0],
                borderColor: '#f39c12'
            }]
        }
    });
    
    // Inicializar gráficas detalladas
    charts.hrDetailed = new Chart(document.getElementById('detailedHrChart'), {
        ...detailedChartConfig,
        data: {
            ...detailedChartConfig.data,
            datasets: [{
                ...detailedChartConfig.data.datasets[0],
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)'
            }]
        }
    });
    
    charts.spo2Detailed = new Chart(document.getElementById('detailedSpo2Chart'), {
        ...detailedChartConfig,
        data: {
            ...detailedChartConfig.data,
            datasets: [{
                ...detailedChartConfig.data.datasets[0],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)'
            }]
        }
    });
    
    charts.tempDetailed = new Chart(document.getElementById('detailedTempChart'), {
        ...detailedChartConfig,
        data: {
            ...detailedChartConfig.data,
            datasets: [{
                ...detailedChartConfig.data.datasets[0],
                borderColor: '#f39c12',
                backgroundColor: 'rgba(243, 156, 18, 0.1)'
            }]
        }
    });
}

// Inicializar mapa
function initializeMap() {
    patientMap = new google.maps.Map(document.getElementById('patientMap'), {
        zoom: 15,
        center: { lat: 19.4326, lng: -99.1332 }, // CDMX por defecto
        mapTypeControl: true,
        streetViewControl: false,
        styles: [
            {
                featureType: 'poi',
                stylers: [{ visibility: 'off' }]
            }
        ]
    });
    
    mapMarker = new google.maps.Marker({
        map: patientMap,
        animation: google.maps.Animation.DROP,
        icon: {
            url: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDJDNy41ODYgMiA0IDUuNTg2IDQgMTBDNCAxNS4yNzQgOS4zNzMgMjEuNDYgMTEuMTI2IDIzLjI0NkMxMS4zNzkgMjMuNDg3IDExLjY4NyAyMy42IDEyIDIzLjZDMTIuMzEzIDIzLjYgMTIuNjIxIDIzLjQ4NyAxMi44NzQgMjMuMjQ2QzE0LjYyNyAyMS40NiAyMCAxNS4yNzQgMjAgMTBDMjAgNS41ODYgMTYuNDE0IDIgMTIgMloiIGZpbGw9IiNlNzRjM2MiLz4KPHBhdGggZD0iTTEyIDEzQzEzLjY1NjkgMTMgMTUgMTEuNjU2OSAxNSAxMEMxNSA4LjM0MzE1IDEzLjY1NjkgNyAxMiA3QzEwLjM0MzEgNyA5IDguMzQzMTUgOSAxMEM5IDExLjY1NjkgMTAuMzQzMSAxMyAxMiAxM1oiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPg==',
            scaledSize: new google.maps.Size(40, 40),
            anchor: new google.maps.Point(20, 40)
        }
    });
}

// Iniciar actualizaciones en tiempo real
function startRealTimeUpdates() {
    updateAllData();
    updateInterval = setInterval(updateAllData, config.updateInterval);
}

// Actualizar todos los datos
async function updateAllData() {
    try {
        await Promise.all([
            updateCurrentReadings(),
            updateChartData(),
            updateAlerts()
        ]);
    } catch (error) {
        console.error('Error en actualización:', error);
        showAlert('Error al actualizar datos', 'error');
    }
}

// Actualizar lecturas actuales
async function updateCurrentReadings() {
    const response = await fetch(`${apiBaseUrl}?device=${currentDevice}`);
    const data = await response.json();
    
    if (data.success && data.reading) {
        const reading = data.reading;
        
        // Actualizar valores
        updateMetric('hr', reading.lectura_FC, 60, 100);
        updateMetric('spo2', reading.lectura_SpO2, 90, 100);
        updateMetric('temp', reading.lectura_temperatura, 35.5, 37.5);
        
        // Actualizar ubicación
        if (reading.gps_lat && reading.gps_lon) {
            updateLocation(reading.gps_lat, reading.gps_lon, reading.fecha_lectura);
        }
        
        // Actualizar gráficas mini
        updateMiniChart(charts.hrMini, reading.lectura_FC);
        updateMiniChart(charts.spo2Mini, reading.lectura_SpO2);
        updateMiniChart(charts.tempMini, reading.lectura_temperatura);
    }
}

// Actualizar datos de gráficas
async function updateChartData() {
    const response = await fetch(`${apiBaseUrl}?device=${currentDevice}&chart=true&hours=${config.chartHistory}`);
    const data = await response.json();
    
    if (data.success && data.chartData) {
        const chartData = data.chartData;
        
        // Actualizar gráficas detalladas
        updateDetailedChart(charts.hrDetailed, chartData.labels, chartData.heartRate, '#e74c3c');
        updateDetailedChart(charts.spo2Detailed, chartData.labels, chartData.spO2, '#3498db');
        updateDetailedChart(charts.tempDetailed, chartData.labels, chartData.temperature, '#f39c12');
    }
}

// Actualizar alertas - Versión mejorada
async function updateAlerts() {
    try {
        console.log('Actualizando alertas...');
        
        const response = await fetch(`api/alerts.php?device=${currentDevice}&status=true`);
        
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Alertas recibidas:', data);
        
        if (data.success) {
            displayAlerts(data.activeAlerts || []);
        } else {
            console.warn('Error en respuesta de alertas:', data.message);
        }
    } catch (error) {
        console.error('Error al cargar alertas:', error);
        // No mostrar error al usuario para no ser intrusivo
    }
}

// Actualizar métrica individual
function updateMetric(type, value, min, max) {
    const valueElement = document.getElementById(`${type}Value`);
    const statusElement = document.getElementById(`${type}Status`);
    
    if (valueElement && statusElement && value !== null) {
        valueElement.textContent = value;
        
        let status = 'normal';
        let statusText = 'Normal';
        
        if (value < min || value > max) {
            status = 'critical';
            statusText = 'Crítico';
        } else if (value < min * 1.1 || value > max * 0.9) {
            status = 'warning';
            statusText = 'Alerta';
        }
        
        statusElement.textContent = statusText;
        statusElement.className = `metric-status ${status}`;
        
        // Animación para valores críticos
        if (status === 'critical') {
            valueElement.classList.add('pulse');
        } else {
            valueElement.classList.remove('pulse');
        }
    }
}

// Actualizar gráfica mini
function updateMiniChart(chart, newValue) {
    if (newValue === null) return;
    
    const maxDataPoints = 10;
    
    // Agregar nuevo dato
    chart.data.labels.push('');
    chart.data.datasets[0].data.push(newValue);
    
    // Mantener máximo número de puntos
    if (chart.data.labels.length > maxDataPoints) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
    }
    
    chart.update('none');
}

// Actualizar gráfica detallada
function updateDetailedChart(chart, labels, data, color) {
    chart.data.labels = labels;
    chart.data.datasets[0].data = data;
    chart.data.datasets[0].borderColor = color;
    chart.data.datasets[0].backgroundColor = color + '20';
    chart.update();
}

// Actualizar ubicación en mapa
function updateLocation(lat, lng, timestamp) {
    const position = new google.maps.LatLng(parseFloat(lat), parseFloat(lng));
    
    // Actualizar marcador
    mapMarker.setPosition(position);
    
    // Centrar mapa
    patientMap.setCenter(position);
    
    // Actualizar información
    document.getElementById('lastLocationUpdate').textContent = 
        new Date(timestamp).toLocaleString();
    document.getElementById('currentCoordinates').textContent = 
        `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
}

// Mostrar alertas - Versión mejorada
function displayAlerts(alerts) {
    const container = document.getElementById('liveAlerts');
    
    if (!container) {
        console.error('Contenedor de alertas no encontrado');
        return;
    }
    
    if (!alerts || alerts.length === 0) {
        container.innerHTML = '<div class="alert info">No hay alertas activas</div>';
        return;
    }
    
    let html = '';
    alerts.forEach(alert => {
        const alertType = alert.tipo_alerta === 'medica' ? 'critical' : 'warning';
        const icon = alert.tipo_alerta === 'medica' ? 'fa-ambulance' : 'fa-map-marker-alt';
        const title = alert.tipo_alerta === 'medica' ? 'Emergencia Médica' : 'Paciente Extraviado';
        
        html += `
            <div class="alert-item ${alertType} fade-in">
                <div class="alert-header">
                    <strong><i class="fas ${icon}"></i> ${title}</strong>
                    <span class="alert-status">${alert.estado || 'PENDIENTE'}</span>
                </div>
                <p>${alert.descripcion || 'Alerta activada'}</p>
                <small>Iniciada: ${new Date(alert.fecha_creacion).toLocaleString()}</small>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Usar la API que guarda en BD
async function triggerMedicalAlert() {
    if (!confirm('¿Estás seguro de activar la alerta médica de emergencia? Se notificará a los servicios de emergencia.')) {
        return;
    }
    
    try {
        const response = await fetch('api/alerts_working.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=medical&device=' + currentDevice
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(updateAlerts, 1000);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error de conexión: ' + error.message, 'error');
    }
}

async function triggerMissingAlert() {
    if (!confirm('¿Estás seguro de reportar al paciente como extraviado? Se activará la búsqueda inmediata.')) {
        return;
    }
    
    try {
        const response = await fetch('api/alerts_working.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=missing&device=' + currentDevice
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(updateAlerts, 1000);
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    } catch (error) {
        showAlert('Error de conexión: ' + error.message, 'error');
    }
}

// Limpiar intervalo al salir de la página
window.addEventListener('beforeunload', () => {
    if (updateInterval) {
        clearInterval(updateInterval);
    }
});

// Debug: Verificar que las funciones estén disponibles
console.log('✅ monitoring.js cargado');
console.log('triggerMedicalAlert disponible:', typeof triggerMedicalAlert);
console.log('triggerMissingAlert disponible:', typeof triggerMissingAlert);
console.log('showAlert disponible:', typeof showAlert);
console.log('currentDevice:', currentDevice);

// Agregar event listeners para debug
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-emergency') || 
        e.target.closest('.btn-emergency')) {
        console.log('Click en botón de emergencia médica');
    }
    if (e.target.classList.contains('btn-warning') || 
        e.target.closest('.btn-warning')) {
        console.log('Click en botón de extravío');
    }
});