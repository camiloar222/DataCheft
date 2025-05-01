<?php



require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

// Redirigir si ya está autenticado
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    
    // Validar nombre
    if (empty($name)) {
        $errors['name'] = 'Por favor, ingresa tu nombre';
    }
    
    // Validar email
    if (empty($email)) {
        $errors['email'] = 'Por favor, ingresa tu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Por favor, ingresa un email válido';
    } elseif (emailExists($email)) {
        $errors['email'] = 'Este email ya está registrado';
    }
    
    // Validar contraseña
    if (empty($password)) {
        $errors['password'] = 'Por favor, ingresa una contraseña';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'La contraseña debe tener al menos 8 caracteres';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número';
    }
    
    // Validar confirmación de contraseña
    if ($password !== $confirmPassword) {
        $errors['confirmPassword'] = 'Las contraseñas no coinciden';
    }
    
    // Si no hay errores, registrar al usuario
    if (empty($errors)) {
        if (registerUser($name, $email, $password)) {
            // Iniciar sesión automáticamente
            $user = getUserByEmail($email);
            $_SESSION['user_id'] = $user['_id'];
            $_SESSION['user_name'] = $user['name'];
            
            // Redirigir a la página principal
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'Error al registrar el usuario. Por favor, inténtalo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Recetas Deliciosas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="form-container">
            <h1 class="form-title">Crear una cuenta</h1>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <form id="registerForm" class="needs-validation" method="POST" novalidate>
                <div class="form-group">
                    <label for="name">Nombre completo</label>
                    <input type="text" id="name" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name ?? ''); ?>">
                    <div id="nameError" class="error-message"><?php echo $errors['name'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <div id="emailError" class="error-message"><?php echo $errors['email'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>">
                    <div id="passwordError" class="error-message"><?php echo $errors['password'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirmar contraseña</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" class="form-control <?php echo isset($errors['confirmPassword']) ? 'is-invalid' : ''; ?>">
                    <div id="confirmPasswordError" class="error-message"><?php echo $errors['confirmPassword'] ?? ''; ?></div>
                </div>
                
                <button type="submit" class="btn btn-block">Registrarse</button>
            </form>
            
            <div class="form-footer">
                <p>¿Ya tienes una cuenta? <a href="login.php">Iniciar sesión</a></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/validation.js"></script>
</body>
</html>