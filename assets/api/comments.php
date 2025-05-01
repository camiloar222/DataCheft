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
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para comentar']);
    exit;
}

// ✅ CORREGIDO: Obtener datos JSON desde php://input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['recipe_id']) || !isset($data['comment']) || empty(trim($data['comment']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$userId = $_SESSION['user_id'];
$recipeId = $data['recipe_id'];
$comment = trim($data['comment']);

// Validar que la receta existe
$recipe = getRecipeById($recipeId);
if (!$recipe) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Receta no encontrada']);
    exit;
}

// Agregar comentario
$result = addComment($userId, $recipeId, $comment);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Comentario agregado correctamente',
        'comment' => $result
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al agregar comentario']);
}
