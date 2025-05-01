<?php
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para realizar esta acción']);
    exit;
}

// Obtener la acción solicitada
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'delete':
        handleDeleteRecipe();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

// Función para manejar la eliminación de recetas
function handleDeleteRecipe() {
    $recipeId = $_POST['recipe_id'] ?? '';
    
    if (empty($recipeId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de receta no válido']);
        exit;
    }
    
    // Obtener la receta
    $recipe = getRecipeById($recipeId);
    
    if (!$recipe) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Receta no encontrada']);
        exit;
    }
    
  

//verificar si el usuario autenticado es el propietario de la receta
if ((string)$recipe['user_id'] !== (string)$_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta receta']);
    header("Location: profile.php"); // o la página que tú quieras
    exit;
}

    
    // Eliminar la imagen si existe
    if (isset($recipe['image_url']) && !empty($recipe['image_url']) && file_exists($recipe['image_url'])) {
        unlink($recipe['image_url']);
    }
    
    // Eliminar la receta
    if (deleteRecipe($recipeId)) {
        echo json_encode([
            'success' => true,
            'message' => 'Receta eliminada correctamente',
            'redirect' => 'profile.php'
            
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la receta']);
    }
}