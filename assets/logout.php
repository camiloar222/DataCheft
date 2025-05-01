<?php
require_once 'includes/db_connect.php';
session_start();

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

// Redirigir a la página principal
header('Location: index.php');
exit;