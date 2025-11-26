<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dispositivos_lista.php");
    exit();
}

require_once 'models/UserModel.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $userModel = new UserModel();
        
        // Verificar si el email ya existe
        $existingUser = $userModel->getUserByEmail($email);
        if ($existingUser) {
            $error = "Este correo electrónico ya está registrado.";
        } else {
            // Crear nuevo usuario
            try {
                $userId = $userModel->createUser($name, $email, $password);
                $success = "¡Registro exitoso! Ahora puedes iniciar sesión.";
            } catch (Exception $e) {
                $error = "Error al crear la cuenta. Por favor, intenta nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Sistema de Monitoreo</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-plus"></i>
                <h1>Crear Cuenta</h1>
                <p>Regístrate para comenzar a monitorear</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <br>
                    <a href="index.php" class="btn-login" style="margin-top: 10px; display: inline-block;">
                        Iniciar Sesión
                    </a>
                </div>
            <?php else: ?>
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Nombre Completo
                        </label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Tu nombre completo" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Correo Electrónico
                        </label>
                        <input type="email" id="email" name="email" required 
                               placeholder="tu@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Contraseña
                        </label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Mínimo 6 caracteres">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirmar Contraseña
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Repite tu contraseña">
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>¿Ya tienes cuenta? <a href="index.php">Inicia sesión aquí</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>