<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: profile.php');
    exit;
}

$recipeId = $_GET['id'];
$userId = $_SESSION['user_id'];

// Obtener la receta
global $recipesCollection;
try {
    $recipe = $recipesCollection->findOne([
        '_id' => new MongoDB\BSON\ObjectId($recipeId),
        'user_id' => new MongoDB\BSON\ObjectId($userId)
    ]);
} catch (Exception $e) {
    header('Location: profile.php');
    exit;
}

if (!$recipe) {
    $_SESSION['error'] = "No se encontró la receta o no tienes permiso para editarla.";
    header('Location: profile.php');
    exit;
}

$categories = getAllCategories();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_recipe'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $ingredients = $_POST['ingredients'] ?? '';
    $instructions = $_POST['instructions'] ?? '';

    if (empty($title)) {
        $errors['title'] = 'El título es obligatorio';
    }
    if (empty($description)) {
        $errors['description'] = 'La descripción es obligatoria';
    }
    if (empty($category)) {
        $errors['category'] = 'Debes seleccionar una categoría';
    }
    if (empty($ingredients)) {
        $errors['ingredients'] = 'Los ingredientes son obligatorios';
    }
    if (empty($instructions)) {
        $errors['instructions'] = 'Las instrucciones son obligatorias';
    }

    $imageUrl = $recipe['image_url'] ?? '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['image']);
        if ($uploadResult['success']) {
            $imageUrl = $uploadResult['url'];
        } else {
            $errors['image'] = $uploadResult['error'];
        }
    }

    if (empty($errors)) {
        $updateData = [
            'title' => $title,
            'description' => $description,
            'category' => $category,
            'ingredients' => $ingredients,
            'instructions' => $instructions,
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];

        if (!empty($imageUrl)) {
            $updateData['image_url'] = $imageUrl;
        }

        $result = $recipesCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($recipeId)],
            ['$set' => $updateData]
        );

        if ($result->getModifiedCount() === 1) {
            $success = true;
            $recipe = $recipesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($recipeId)]);
        } else {
            $errors['general'] = 'No se pudo actualizar la receta. Por favor, inténtalo de nuevo.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Receta - Recetas Deliciosas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* Estilos específicos para la página de edición de recetas */
        .edit-recipe-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 30px;
            margin: 30px 0;
        }
        
        .edit-recipe-header {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .edit-recipe-header h1 {
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .edit-recipe-form {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 20px;
        }
        
        .form-group {
            flex: 1;
            min-width: 250px;
        }
        
        .form-group.full-width {
            flex: 0 0 100%;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .current-image {
            margin-top: 10px;
            text-align: center;
        }
        
        .current-image img {
            max-width: 200px;
            max-height: 200px;
            border-radius: var(--border-radius);
            border: 1px solid var(--medium-gray);
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        /* Alertas */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="edit-recipe-container">
            <div class="edit-recipe-header">
                <h1>Editar Receta</h1>
                <p>Actualiza la información de tu receta</p>
            </div>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                La receta se ha actualizado correctamente.
            </div>
            <?php endif; ?>
            
            <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error">
                <?php echo $errors['general']; ?>
            </div>
            <?php endif; ?>
            
            <form class="edit-recipe-form" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Título de la receta *</label>
                        <input type="text" id="title" name="title" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($recipe['title']); ?>">
                        <div class="error-message"><?php echo $errors['title'] ?? ''; ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Categoría *</label>
                        <select id="category" name="category" class="form-control <?php echo isset($errors['category']) ? 'is-invalid' : ''; ?>">
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $recipe['category'] === $cat['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message"><?php echo $errors['category'] ?? ''; ?></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="description">Descripción *</label>
                        <textarea id="description" name="description" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo htmlspecialchars($recipe['description']); ?></textarea>
                        <div class="error-message"><?php echo $errors['description'] ?? ''; ?></div>
                    </div>
                </div>

                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="ingredients">Ingredientes *</label>
                        <textarea id="ingredients" name="ingredients" class="form-control <?php echo isset($errors['ingredients']) ? 'is-invalid' : ''; ?>" placeholder="Escribe cada ingrediente en una línea nueva"><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
                        <div class="error-message"><?php echo $errors['ingredients'] ?? ''; ?></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="instructions">Instrucciones *</label>
                        <textarea id="instructions" name="instructions" class="form-control <?php echo isset($errors['instructions']) ? 'is-invalid' : ''; ?>" placeholder="Escribe cada paso en una línea nueva"><?php echo htmlspecialchars($recipe['instructions']); ?></textarea>
                        <div class="error-message"><?php echo $errors['instructions'] ?? ''; ?></div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="image">Imagen de la receta</label>
                        <input type="file" id="image" name="image" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" accept="image/*">
                        <small class="form-text text-muted">Deja este campo vacío si no deseas cambiar la imagen actual.</small>
                        <div class="error-message"><?php echo $errors['image'] ?? ''; ?></div>
                        
                        <?php if (isset($recipe['image_url']) && !empty($recipe['image_url'])): ?>
                        <div class="current-image">
                            <p>Imagen actual:</p>
                            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="profile.php" class="btn btn-outline">Cancelar</a>
                    <button type="submit" name="update_recipe" class="btn">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Script para validación del formulario en el lado del cliente
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.edit-recipe-form');
            
            form.addEventListener('submit', function(event) {
                let hasErrors = false;
                
                // Validar título
                const title = document.getElementById('title');
                if (title.value.trim() === '') {
                    showError(title, 'El título es obligatorio');
                    hasErrors = true;
                } else {
                    clearError(title);
                }
                
                // Validar categoría
                const category = document.getElementById('category');
                if (category.value === '') {
                    showError(category, 'Debes seleccionar una categoría');
                    hasErrors = true;
                } else {
                    clearError(category);
                }
                
                // Validar descripción
                const description = document.getElementById('description');
                if (description.value.trim() === '') {
                    showError(description, 'La descripción es obligatoria');
                    hasErrors = true;
                } else {
                    clearError(description);
                }
                
            
                // Validar ingredientes e instrucciones
                const ingredients = document.getElementById('ingredients');
                if (ingredients.value.trim() === '') {
                    showError(ingredients, 'Los ingredientes son obligatorios');
                    hasErrors = true;
                } else {
                    clearError(ingredients);
                }
                
                const instructions = document.getElementById('instructions');
                if (instructions.value.trim() === '') {
                    showError(instructions, 'Las instrucciones son obligatorias');
                    hasErrors = true;
                } else {
                    clearError(instructions);
                }
                
                // Si hay errores, prevenir el envío del formulario
                if (hasErrors) {
                    event.preventDefault();
                    // Scroll al primer error
                    const firstError = document.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            // Función para mostrar error
            function showError(element, message) {
                element.classList.add('is-invalid');
                const errorDiv = element.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error-message')) {
                    errorDiv.textContent = message;
                }
            }
            
            // Función para limpiar error
            function clearError(element) {
                element.classList.remove('is-invalid');
                const errorDiv = element.nextElementSibling;
                if (errorDiv && errorDiv.classList.contains('error-message')) {
                    errorDiv.textContent = '';
                }
            }
        });
    </script>
</body>
</html>