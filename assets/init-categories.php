<?php
require_once 'includes/db_connect.php';

// Categorías iniciales
$initialCategories = [
    [
        'name' => 'Postres',
        'icon_url' => 'images/Postre.png',
        'recipe_count' => 0
    ],
    [
        'name' => 'Platos Fuertes',
        'icon_url' => 'images/fuerte.png',
        'recipe_count' => 0
    ],
    [
        'name' => 'Ensaladas',
        'icon_url' => 'images/ensalada.png',
        'recipe_count' => 0
    ],
    [
        'name' => 'Sopas',
        'icon_url' => 'images/sopa.png',
        'recipe_count' => 0
    ],
    [
        'name' => 'Vegano',
        'icon_url' => 'images/vegano.png',
        'recipe_count' => 0
    ],
    
];

// Insertar categorías
foreach ($initialCategories as $category) {
    try {
        $categoriesCollection->insertOne($category);
        echo "Categoría '{$category['name']}' agregada correctamente.<br>";
    } catch (Exception $e) {
        echo "Error al agregar la categoría '{$category['name']}': " . $e->getMessage() . "<br>";
    }
}

echo "Proceso completado.";