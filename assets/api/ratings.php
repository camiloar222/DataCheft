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

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para calificar']);
    exit;
}

// Obtener datos de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['recipe_id']) || !isset($data['rating']) || !is_numeric($data['rating'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos']);
    exit;
}

$userId = $_SESSION['user_id'];
$recipeId = $data['recipe_id'];
$rating = (int)$data['rating'];

// Validar calificación
if ($rating < 1 || $rating > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'La calificación debe estar entre 1 y 5']);
    exit;
}

// Validar que la receta existe
$recipe = getRecipeById($recipeId);
if (!$recipe) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Receta no encontrada']);
    exit;
}

// Agregar calificación y obtener resultados
$result = rateRecipe($userId, $recipeId, $rating);

// Respuesta exitosa
echo json_encode([
    'success' => true,
    'message' => 'Calificación agregada correctamente',
    'average_rating' => round($result['average'], 1),
    'ratings_count' => $result['count']
]);
