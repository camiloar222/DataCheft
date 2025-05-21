<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener información del usuario
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

if (!$user) {
    // Si no se encuentra el usuario, cerrar sesión y redirigir
    session_destroy();
    header('Location: login.php');
    exit;
}

// Obtener recetas del usuario
global $recipesCollection;
$userRecipes = $recipesCollection->find(['user_id' => new MongoDB\BSON\ObjectId($userId)], [
    'sort' => ['created_at' => -1]
])->toArray();

// Procesar actualización de perfil
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Validar datos
    $name = trim($_POST['name'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validar nombre
    if (empty($name)) {
        $errors['name'] = 'Por favor, ingresa tu nombre';
    }
    
    if (!empty($newPassword) || !empty($confirmPassword)) {

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Por favor, ingresa tu contraseña actual';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors['current_password'] = 'La contraseña actual es incorrecta';
        }
        
        // Validar nueva contraseña
        if (empty($newPassword)) {
            $errors['new_password'] = 'Por favor, ingresa la nueva contraseña';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'La contraseña debe tener al menos 8 caracteres';
        } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/\d/', $newPassword)) {
            $errors['new_password'] = 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número';
        }
        
        // Validar confirmación de contraseña
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden';
        }
    }
    
    // Si no hay errores, actualizar perfil
    if (empty($errors)) {
        global $usersCollection;
        
        $updateData = [
            'name' => $name,
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        // Si se cambió la contraseña
        if (!empty($newPassword)) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        $result = $usersCollection->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($userId)],
            ['$set' => $updateData]
        );
        
        if ($result->getModifiedCount() === 1) {
            $success = true;
            // Actualizar nombre en la sesión
            $_SESSION['user_name'] = $name;
            // Actualizar datos del usuario
            $user = getUserById($userId);
        } else {
            $errors['general'] = 'No se pudo actualizar el perfil. Por favor, inténtalo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Recetas Deliciosas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* Estilos específicos para la página de perfil */
        .profile-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin: 30px 0;
            overflow: hidden;
        }

        .profile-header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px;
            text-align: center;
        }

        .profile-header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .profile-content {
            display: flex;
            flex-wrap: wrap;
        }

        .profile-sidebar {
            flex: 0 0 250px;
            padding: 20px;
            border-right: 1px solid var(--medium-gray);
            background-color: var(--light-gray);
        }

        .profile-main {
            flex: 1;
            min-width: 300px;
            padding: 20px;
        }

        .profile-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }

        .profile-info h2 {
            margin: 0 0 10px;
            font-size: 1.5rem;
        }

        .profile-email {
            color: var(--dark-gray);
            margin-bottom: 5px;
            word-break: break-all;
        }

        .profile-date {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .stat {
            text-align: center;
            padding: 0 10px;
        }

        .stat-value {
            display: block;
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }

        .profile-actions {
            text-align: center;
        }

        .profile-tabs {
            display: flex;
            border-bottom: 1px solid var(--medium-gray);
            margin-bottom: 20px;
        }

        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            color: var(--dark-gray);
            position: relative;
        }

        .tab-button.active {
            color: var(--primary-color);
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-color);
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .no-recipes {
            text-align: center;
            padding: 30px;
            color: var(--dark-gray);
        }

        .user-recipes {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .user-recipe-card {
            display: flex;
            background-color: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .user-recipe-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .user-recipe-card .recipe-image {
            flex: 0 0 100px;
            height: 100px;
        }

        .user-recipe-card .recipe-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-recipe-card .recipe-info {
            flex: 1;
            padding: 10px 15px;
            display: flex;
            flex-direction: column;
        }

        .user-recipe-card h3 {
            margin: 0 0 5px;
            font-size: 1.1rem;
        }

        .user-recipe-card .recipe-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.8rem;
        }

        .user-recipe-card .recipe-actions {
            margin-top: auto;
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .settings-form {
            max-width: 600px;
        }

        .settings-form h2 {
            margin-bottom: 20px;
            font-size: 1.5rem;
        }

        .settings-form h3 {
            margin: 30px 0 10px;
            font-size: 1.2rem;
        }

        /* Modal de confirmación */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 0;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
        }

        .modal-header {
            padding: 15px 20px;
            background-color: var(--light-gray);
            border-bottom: 1px solid var(--medium-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .close-modal {
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            background-color: var(--light-gray);
            border-top: 1px solid var(--medium-gray);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
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

        /* Responsive */
        @media (max-width: 768px) {
            .profile-content {
                flex-direction: column;
            }
            
            .profile-sidebar {
                border-right: none;
                border-bottom: 1px solid var(--medium-gray);
            }
            
            .user-recipes {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="profile-container">
            <div class="profile-header">
                <h1>Mi Perfil</h1>
            </div>
            
            <div class="profile-content">
                <div class="profile-sidebar">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <span class="avatar-placeholder"><?php echo substr($user['name'], 0, 1); ?></span>
                        </div>
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="profile-date">Miembro desde: <?php echo $user['created_at']->toDateTime()->format('d/m/Y'); ?></p>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-value"><?php echo count($userRecipes); ?></span>
                            <span class="stat-label">Recetas</span>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="add-recipe.php" class="btn">Agregar Nueva Receta</a>
                    </div>
                </div>
                
                <div class="profile-main">
                    <div class="profile-tabs">
                        <button class="tab-button active" data-tab="recipes">Mis Recetas</button>
                        <button class="tab-button" data-tab="settings">Configuración</button>
                    </div>
                    
                    <div class="tab-content">
                        <div id="recipes" class="tab-pane active">
                            <?php if (empty($userRecipes)): ?>
                            <div class="no-recipes">
                                <p>Aún no has publicado ninguna receta.</p>
                                <a href="add-recipe.php" class="btn">Agregar Primera Receta</a>
                            </div>
                            <?php else: ?>
                            <div class="user-recipes">
                                <?php foreach ($userRecipes as $recipe): ?>
                                <div class="user-recipe-card">
                                    <div class="recipe-image">
                                        <?php if (isset($recipe['image_url']) && !empty($recipe['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                                        <?php else: ?>
                                        <img src="assets/images/default-recipe.jpg" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                                        <?php endif; ?>
                                    </div>
                                    <div class="recipe-info">
                                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                        <div class="recipe-meta">
                                            <span class="category"><?php echo htmlspecialchars($recipe['category']); ?></span>
                                            <span class="rating">
                                                <?php echo generateStarRating($recipe['average_rating']); ?>
                                                (<?php echo $recipe['ratings_count']; ?>)
                                            </span>
                                        </div>
                                        <div class="recipe-actions">
                                            <a href="recipe-details.php?id=<?php echo $recipe['_id']; ?>" class="btn btn-sm">Ver</a>
                                            <a href="edit-recipe.php?id=<?php echo $recipe['_id']->__toString(); ?>" class="btn btn-sm btn-secondary">Editar</a>
                                            <button class="btn btn-sm btn-danger delete-recipe" data-id="<?php echo $recipe['_id']; ?>">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div id="settings" class="tab-pane">
                            <div class="settings-form">
                                <h2>Actualizar Perfil</h2>
                                
                                <?php if ($success): ?>
                                <div class="alert alert-success">
                                    Perfil actualizado correctamente.
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($errors['general'])): ?>
                                <div class="alert alert-error">
                                    <?php echo $errors['general']; ?>
                                </div>
                                <?php endif; ?>
                                
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="form-group">
                                        <label for="name">Nombre completo</label>
                                        <input type="text" id="name" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($user['name']); ?>">
                                        <div class="error-message"><?php echo $errors['name'] ?? ''; ?></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="email">Correo electrónico</label>
                                        <input type="email" id="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        <small class="form-text text-muted">No puedes cambiar tu correo electrónico.</small>
                                    </div>
                                    
                                    <h3>Cambiar Contraseña</h3>
                                    <p class="form-text text-muted">Deja estos campos en blanco si no deseas cambiar tu contraseña.</p>
                                    
                                    <div class="form-group">
                                        <label for="current_password">Contraseña actual</label>
                                        <input type="password" id="current_password" name="current_password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>">
                                        <div class="error-message"><?php echo $errors['current_password'] ?? ''; ?></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="new_password">Nueva contraseña</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>">
                                        <div class="error-message"><?php echo $errors['new_password'] ?? ''; ?></div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="confirm_password">Confirmar nueva contraseña</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>">
                                        <div class="error-message"><?php echo $errors['confirm_password'] ?? ''; ?></div>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn">Guardar Cambios</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
   <!-- Modal de confirmación para eliminar receta -->
<div class="modal" id="deleteModal" style="display: none; transition: opacity 0.3s ease;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar eliminación</h3>
            <span class="close-modal" style="cursor: pointer;">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar esta receta? Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" id="cancelDelete">Cancelar</button>
            <!-- Form sin atributos de submit tradicionales -->
            <form id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="recipe_id" id="deleteRecipeId">
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tabs
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Modal
    const deleteModal = document.getElementById('deleteModal');
    const deleteButtons = document.querySelectorAll('.delete-recipe');
    const closeModal = document.querySelector('.close-modal');
    const cancelDelete = document.getElementById('cancelDelete');
    const deleteForm = document.getElementById('deleteForm');
    const deleteRecipeId = document.getElementById('deleteRecipeId');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const recipeId = this.getAttribute('data-id');
            deleteRecipeId.value = recipeId;
            deleteModal.style.opacity = 0;
            deleteModal.style.display = 'block';
            setTimeout(() => {
                deleteModal.style.opacity = 1;
            }, 10);
        });
    });

    function closeDeleteModal() {
        deleteModal.style.opacity = 0;
        setTimeout(() => {
            deleteModal.style.display = 'none';
        }, 300);
    }

    closeModal.addEventListener('click', closeDeleteModal);

    cancelDelete.addEventListener('click', function(e) {
        e.preventDefault();
        closeDeleteModal();
    });

    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });

    // Enviar formulario con fetch
    deleteForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(deleteForm);
        const deleteButton = deleteForm.querySelector('button[type="submit"]');
        const originalText = deleteButton.textContent;

        deleteButton.textContent = 'Eliminando...';
        deleteButton.disabled = true;

        fetch('api/recipes.php', {
            method: 'POST',
            body: formData
        })
        .then(async res => {
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Error al eliminar la receta');
                    deleteButton.textContent = originalText;
                    deleteButton.disabled = false;
                    closeDeleteModal();
                }
            } catch (err) {
                console.error('Respuesta no JSON:', text);
                alert('Respuesta inesperada del servidor');
                deleteButton.textContent = originalText;
                deleteButton.disabled = false;
                closeDeleteModal();
            }
        })
        .catch(err => {
            console.error('Error al eliminar receta:', err);
            alert('Hubo un problema al eliminar la receta');
            deleteButton.textContent = originalText;
            deleteButton.disabled = false;
            closeDeleteModal();
        });
    });
});
</script>
