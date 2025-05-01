<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener categorías para el formulario
$categories = getAllCategories();

$errors = [];
$success = false;

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    
    // Validar título
    if (empty($title)) {
        $errors['title'] = 'Por favor, ingresa un título para la receta';
    }
    
    // Validar categoría
    if (empty($category)) {
        $errors['category'] = 'Por favor, selecciona una categoría';
    }
    
    // Validar descripción
    if (empty($description)) {
        $errors['description'] = 'Por favor, ingresa una descripción';
    }
    
    // Validar ingredientes
    if (empty($ingredients)) {
        $errors['ingredients'] = 'Por favor, ingresa los ingredientes';
    }
    
    // Validar instrucciones
    if (empty($instructions)) {
        $errors['instructions'] = 'Por favor, ingresa las instrucciones';
    }
    
    // Procesar imagen si se ha subido
    $imageUrl = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors['image'] = 'El archivo debe ser una imagen (JPEG, PNG o GIF)';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors['image'] = 'La imagen no debe superar los 5MB';
        } else {
            // Generar nombre único para la imagen
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $extension;
            $uploadPath = 'uploads/recipes/' . $fileName;
            
            // Crear directorio si no existe
            if (!is_dir('uploads/recipes')) {
                mkdir('uploads/recipes', 0777, true);
            }
            
            // Mover archivo
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $imageUrl = $uploadPath;
            } else {
                $errors['image'] = 'Error al subir la imagen. Por favor, inténtalo de nuevo.';
            }
        }
    }
    
    // Si no hay errores, crear la receta
    if (empty($errors)) {
        $recipeId = createRecipe(
            $_SESSION['user_id'],
            $title,
            $category,
            $description,
            $ingredients,
            $instructions,
            $imageUrl
        );
        
        if ($recipeId) {
            $success = true;
            // Redirigir a la página de la receta
            header('Location: recipe-details.php?id=' . $recipeId);
            exit;
        } else {
            $errors['general'] = 'Error al crear la receta. Por favor, inténtalo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Receta - Recetas Deliciosas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="form-container recipe-form-container">
            <h1 class="form-title">Agregar Nueva Receta</h1>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-error">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>
            
            <form id="recipeForm" class="needs-validation" method="POST" enctype="multipart/form-data" novalidate>
                <div class="form-group">
                    <label for="title">Título de la receta</label>
                    <input type="text" id="title" name="title" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($title ?? ''); ?>">
                    <div id="titleError" class="error-message"><?php echo $errors['title'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="category">Categoría</label>
                    <select id="category" name="category" class="form-control <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>">
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo ($category ?? '') === $cat['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="categoryError" class="error-message"><?php echo $errors['category'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="description">Descripción</label>
                    <textarea id="description" name="description" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    <div id="descriptionError" class="error-message"><?php echo $errors['description'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="ingredients">Ingredientes (uno por línea)</label>
                    <textarea id="ingredients" name="ingredients" class="form-control <?php echo isset($errors['ingredients']) ? 'is-invalid' : ''; ?>" rows="6"><?php echo htmlspecialchars($ingredients ?? ''); ?></textarea>
                    <div id="ingredientsError" class="error-message"><?php echo $errors['ingredients'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="instructions">Instrucciones (uno por línea)</label>
                    <textarea id="instructions" name="instructions" class="form-control <?php echo isset($errors['instructions']) ? 'is-invalid' : ''; ?>" rows="8"><?php echo htmlspecialchars($instructions ?? ''); ?></textarea>
                    <div id="instructionsError" class="error-message"><?php echo $errors['instructions'] ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="image">Imagen de la receta (opcional)</label>
                    <input type="file" id="image" name="image" class="form-control-file <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>">
                    <small class="form-text text-muted">Formatos permitidos: JPEG, PNG, GIF. Tamaño máximo: 5MB.</small>
                    <div id="imageError" class="error-message"><?php echo $errors['image'] ?? ''; ?></div>
                </div>
                
                <button type="submit" class="btn btn-block">Publicar Receta</button>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/validation.js"></script>
</body>
</html>