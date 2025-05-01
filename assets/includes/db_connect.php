<?php

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

// Configuración de la conexión
$mongoClient = new Client("mongodb://localhost:27017");
$database = $mongoClient->data_cheft; // Nombre de la base de datos

// Colecciones
$usersCollection = $database->users;
$recipesCollection = $database->recipes;
$categoriesCollection = $database->categories;
$commentsCollection = $database->comments;
$ratingsCollection = $database->ratings;


// Función para verificar la conexión
function testConnection() {
    global $mongoClient;
    try {
        $mongoClient->listDatabases();
        return true;
    } catch (Exception $e) {
        error_log("Error de conexión a MongoDB: " . $e->getMessage());
        return false;
    }
}

