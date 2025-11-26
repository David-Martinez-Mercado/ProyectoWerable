<?php
session_start();

// Manejar logout directamente
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Limpiar todas las variables de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Redirigir al login
    header("Location: ../index.php");
    exit();
}

// Para otras acciones, requerir el modelo
require_once '../models/UserModel.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            handleLogin();
            break;
            
        case 'register':
            handleRegister();
            break;
            
        case 'profile':
            handleProfile();
            break;
            
        case 'update':
            handleUpdate();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email y contraseña requeridos']);
        return;
    }
    
    $userModel = new UserModel();
    $user = $userModel->getUserByEmail($email);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['tipo_usuario'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user['id'],
                'name' => $user['nombre'],
                'email' => $user['email'],
                'type' => $user['tipo_usuario']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas']);
    }
}

function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }
    
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres']);
        return;
    }
    
    $userModel = new UserModel();
    
    // Verificar si el email ya existe
    $existingUser = $userModel->getUserByEmail($email);
    if ($existingUser) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        return;
    }
    
    // Crear nuevo usuario
    $userId = $userModel->createUser($name, $email, $password);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario registrado exitosamente',
        'user_id' => $userId
    ]);
}

function handleProfile() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        return;
    }
    
    $userModel = new UserModel();
    $user = $userModel->getUserById($_SESSION['user_id']);
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        return;
    }
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'name' => $user['nombre'],
            'email' => $user['email'],
            'type' => $user['tipo_usuario'],
            'created_at' => $user['fecha_creacion']
        ]
    ]);
}

function handleUpdate() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    
    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nombre y email requeridos']);
        return;
    }
    
    $userModel = new UserModel();
    $affected = $userModel->updateUser($_SESSION['user_id'], $name, $email);
    
    if ($affected > 0) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
    }
}
?>