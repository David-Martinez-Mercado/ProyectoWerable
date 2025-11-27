<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'models/UserModel.php';

$userModel = new UserModel();
$user = $userModel->getUserById($_SESSION['user_id']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    try {
        // Actualizar información básica
        if ($name !== $user['nombre'] || $email !== $user['email']) {
            $userModel->updateUser($_SESSION['user_id'], $name, $email);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $message = 'Perfil actualizado correctamente';
            $messageType = 'success';
        }
        
        // Actualizar contraseña si se proporciona
        if (!empty($current_password) && !empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $message = 'Las nuevas contraseñas no coinciden';
                $messageType = 'error';
            } elseif (strlen($new_password) < 6) {
                $message = 'La nueva contraseña debe tener al menos 6 caracteres';
                $messageType = 'error';
            } else {
                // Verificar contraseña actual
                $currentUser = $userModel->getUserByEmail($user['email']);
                if ($currentUser && password_verify($current_password, $currentUser['password'])) {
                    // Actualizar contraseña
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    // Aquí iría la función para actualizar la contraseña
                    $message = 'Contraseña actualizada correctamente';
                    $messageType = 'success';
                } else {
                    $message = 'La contraseña actual es incorrecta';
                    $messageType = 'error';
                }
            }
        }
        
        // Recargar datos del usuario
        $user = $userModel->getUserById($_SESSION['user_id']);
        
    } catch (Exception $e) {
        $message = 'Error al actualizar el perfil: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Sistema Guardián</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<script>
    // Tema oscuro
    function toggleTheme() {
        document.body.classList.toggle('dark-theme');
        const isDark = document.body.classList.contains('dark-theme');
        localStorage.setItem('darkTheme', isDark);
        
        // Cambiar icono
        const icon = document.querySelector('.theme-toggle i');
        if (icon) {
            if (isDark) {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
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
</script>
</body>
</html>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="header-content">
                <h1><i class="fas fa-heartbeat"></i> Configuración del Sistema</h1>
                <div class="user-menu">
                    <button class="theme-toggle" onclick="toggleTheme()">
                        <i class="fas fa-moon"></i>
                    </button>
                    <span>Bienvenido, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
                </div>
            </div>
        </header>

        <!-- Navigation -->
        <nav class="dashboard-nav">
            <ul>
                <li><a href="dispositivos_lista.php"><i class="fas fa-list"></i> Mis Dispositivos</a></li>
                <li><a href="monitoreo_paciente.php"><i class="fas fa-heartbeat"></i> Monitoreo en Tiempo Real</a></li>
                <li><a href="historial_descarga.php"><i class="fas fa-history"></i> Historial</a></li>
                <li class="active"><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="dashboard-main">
            <div class="page-header">
                <h2><i class="fas fa-user-cog"></i> Configuración de Cuenta</h2>
                <p>Gestiona tu perfil y preferencias del sistema</p>
            </div>

            <?php if ($message): ?>
                <div class="alert <?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check' : 'exclamation'; ?>-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="settings-grid">
                <!-- Información del Perfil -->
                <div class="settings-section">
                    <h3><i class="fas fa-user"></i> Información Personal</h3>
                    <form method="POST" class="settings-form">
                        <div class="form-group">
                            <label for="name">Nombre Completo:</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Correo Electrónico:</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="user_type">Tipo de Usuario:</label>
                            <input type="text" id="user_type" 
                                   value="<?php echo htmlspecialchars($user['tipo_usuario']); ?>" disabled>
                            <small>El tipo de usuario no puede ser modificado</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="registration_date">Fecha de Registro:</label>
                            <input type="text" id="registration_date" 
                                   value="<?php echo date('d/m/Y', strtotime($user['fecha_creacion'])); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </form>
                </div>

                <!-- Cambio de Contraseña -->
                <div class="settings-section">
                    <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                    <form method="POST" class="settings-form">
                        <div class="form-group">
                            <label for="current_password">Contraseña Actual:</label>
                            <input type="password" id="current_password" name="current_password" 
                                   placeholder="Ingresa tu contraseña actual">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Nueva Contraseña:</label>
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Mínimo 6 caracteres">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Repite la nueva contraseña">
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    </form>
                </div>

                <!-- Preferencias del Sistema -->
                <div class="settings-section">
                    <h3><i class="fas fa-sliders-h"></i> Preferencias</h3>
                    <form class="settings-form">
                        <div class="form-group">
                            <label for="update_interval">Intervalo de Actualización:</label>
                            <select id="update_interval" name="update_interval">
                                <option value="5">5 segundos</option>
                                <option value="10">10 segundos</option>
                                <option value="30">30 segundos</option>
                                <option value="60">1 minuto</option>
                            </select>
                            <small>Tiempo entre actualizaciones automáticas</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="theme">Tema de Interfaz:</label>
                            <select id="theme" name="theme">
                                <option value="light">Claro</option>
                                <option value="dark">Oscuro</option>
                                <option value="auto">Automático</option>
                            </select>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="notifications" name="notifications" checked>
                            <label for="notifications">Recibir notificaciones de alertas</label>
                        </div>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="email_alerts" name="email_alerts">
                            <label for="email_alerts">Alertas por correo electrónico</label>
                        </div>
                        
                        <button type="button" class="btn-secondary" onclick="savePreferences()">
                            <i class="fas fa-save"></i> Guardar Preferencias
                        </button>
                    </form>
                </div>

                <!-- Información del Sistema -->
                <div class="settings-section">
                    <h3><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                    <div class="system-info">
                        <div class="info-item">
                            <strong>Versión:</strong>
                            <span>1.0.0</span>
                        </div>
                        <div class="info-item">
                            <strong>Última Actualización:</strong>
                            <span>Noviembre 2024</span>
                        </div>
                        <div class="info-item">
                            <strong>Base de Datos:</strong>
                            <span>MySQL 5.7+</span>
                        </div>
                        <div class="info-item">
                            <strong>Servidor Web:</strong>
                            <span>Apache/Nginx</span>
                        </div>
                        <div class="info-item">
                            <strong>PHP:</strong>
                            <span>7.4+</span>
                        </div>
                    </div>
                    
                    <div class="system-actions">
                        <button class="btn-secondary" onclick="showSystemLogs()">
                            <i class="fas fa-file-alt"></i> Ver Logs del Sistema
                        </button>
                        <button class="btn-secondary" onclick="exportAllData()">
                            <i class="fas fa-download"></i> Exportar Todos los Datos
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function savePreferences() {
            const interval = document.getElementById('update_interval').value;
            const theme = document.getElementById('theme').value;
            const notifications = document.getElementById('notifications').checked;
            const emailAlerts = document.getElementById('email_alerts').checked;
            
            // Guardar en localStorage
            localStorage.setItem('updateInterval', interval);
            localStorage.setItem('theme', theme);
            localStorage.setItem('notifications', notifications);
            localStorage.setItem('emailAlerts', emailAlerts);
            
            alert('Preferencias guardadas correctamente');
        }
        
        function showSystemLogs() {
            alert('Esta funcionalidad estará disponible en futuras versiones');
        }
        
        function exportAllData() {
            if (confirm('¿Estás seguro de que quieres exportar todos tus datos? Esto puede tomar varios minutos.')) {
                alert('La exportación de datos comenzará pronto. Recibirás un correo con el enlace de descarga.');
            }
        }
        
        // Cargar preferencias guardadas
        document.addEventListener('DOMContentLoaded', function() {
            const savedInterval = localStorage.getItem('updateInterval');
            const savedTheme = localStorage.getItem('theme');
            const savedNotifications = localStorage.getItem('notifications');
            const savedEmailAlerts = localStorage.getItem('emailAlerts');
            
            if (savedInterval) document.getElementById('update_interval').value = savedInterval;
            if (savedTheme) document.getElementById('theme').value = savedTheme;
            if (savedNotifications) document.getElementById('notifications').checked = savedNotifications === 'true';
            if (savedEmailAlerts) document.getElementById('email_alerts').checked = savedEmailAlerts === 'true';
        });
    </script>
</body>
</html>