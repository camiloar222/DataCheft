<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener la acción solicitada
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

// Función para manejar el inicio de sesión
function handleLogin() {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    // Validar datos
    $errors = [];
    
    if (empty($email)) {
        $errors['email'] = 'Por favor, ingresa tu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Por favor, ingresa un email válido';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Por favor, ingresa tu contraseña';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Verificar credenciales
    $user = verifyLogin($email, $password);
    
    if ($user) {
        // Iniciar sesión
        $_SESSION['user_id'] = $user['_id'];
        $_SESSION['user_name'] = $user['name'];
        
        // Si se seleccionó "Recordarme", establecer cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expires = time() + (30 * 24 * 60 * 60); // 30 días
            
            // Guardar token en la base de datos
            global $usersCollection;
            $usersCollection->updateOne(
                ['_id' => $user['_id']],
                ['$set' => [
                    'remember_token' => $token,
                    'token_expires' => new MongoDB\BSON\UTCDateTime($expires * 1000)
                ]]
            );
            
            // Establecer cookie
            setcookie('remember_token', $token, $expires, '/', '', false, true);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'errors' => ['general' => 'Email o contraseña incorrectos']
        ]);
    }
}

// Función para manejar el registro
function handleRegister() {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validar datos
    $errors = [];
    
    if (empty($name)) {
        $errors['name'] = 'Por favor, ingresa tu nombre';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Por favor, ingresa tu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Por favor, ingresa un email válido';
    } elseif (emailExists($email)) {
        $errors['email'] = 'Este email ya está registrado';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Por favor, ingresa una contraseña';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Las contraseñas no coinciden';
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Registrar usuario
    if (registerUser($name, $email, $password)) {
        // Iniciar sesión automáticamente
        $user = getUserByEmail($email);
        $_SESSION['user_id'] = $user['_id'];
        $_SESSION['user_name'] = $user['name'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Registro exitoso',
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'errors' => ['general' => 'Error al registrar el usuario. Por favor, inténtalo de nuevo.']
        ]);
    }
}

// Función para manejar el cierre de sesión
function handleLogout() {
    // Eliminar cookie de "Recordarme" si existe
    if (isset($_COOKIE['remember_token'])) {
        // Eliminar token de la base de datos
        if (isset($_SESSION['user_id'])) {
            global $usersCollection;
            $usersCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id'])],
                ['$unset' => ['remember_token' => '', 'token_expires' => '']]
            );
        }
        
        // Eliminar cookie
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
    
    // Destruir sesión
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente',
        'redirect' => 'index.php'
    ]);
}