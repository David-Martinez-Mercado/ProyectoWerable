<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dispositivos_lista.php");
    exit();
}

require_once 'models/UserModel.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        $userModel = new UserModel();
        $user = $userModel->getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_type'] = $user['tipo_usuario'];
            
            header("Location: dispositivos_lista.php");
            exit();
        } else {
            $error = "Credenciales incorrectas. Por favor, intenta nuevamente.";
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema Guardián</title>
    <link rel="stylesheet" href="assets/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-heartbeat"></i>
                <h1>Sistema Guardián</h1>
                <p>Inicia sesión en tu cuenta</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
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
                           placeholder="Tu contraseña">
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </button>
            </form>
            
            <div class="login-footer">
                <p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
            </div>
        </div>
    </div>
</body>
</html>
