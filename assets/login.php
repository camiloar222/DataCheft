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
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validar email
    if (empty($email)) {
        $errors['email'] = 'Por favor, ingresa tu email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Por favor, ingresa un email válido';
    }
    
    // Validar contraseña
    if (empty($password)) {
        $errors['password'] = 'Por favor, ingresa tu contraseña';
    }
    
    // Si no hay errores, intentar iniciar sesión
    if (empty($errors)) {
        $user = verifyLogin($email, $password);
        
        if ($user) {
            // Iniciar sesión
            $_SESSION['user_id'] = $user['_id'];
            $_SESSION['user_name'] = $user['name'];
            
            // Redirigir a la página principal
            header('Location: index.php');
            exit;
        } else {
            $errors['general'] = 'Email o contraseña incorrectos';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Recetas Deliciosas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="form-container">
            <h1 class="form-title">Iniciar Sesión</h1>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" class="needs-validation" method="POST" novalidate>
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
                
                <button type="submit" class="btn btn-block">Iniciar Sesión</button>
            </form>
            
            <div class="form-footer">
                <p>¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/validation.js"></script>
</body>
</html>