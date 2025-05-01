<?php

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

// Obtener categoría seleccionada
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;

// Parámetros de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$skip = ($page - 1) * $limit;

// Obtener todas las categorías
$categories = getAllCategories();

// Si hay una categoría seleccionada, obtener sus recetas
$recipes = [];
$totalRecipes = 0;
$totalPages = 0;

if ($selectedCategory) {
    $recipes = getRecipesByCategory($selectedCategory, $limit, $skip);
    $totalRecipes = count(getRecipesByCategory($selectedCategory, 1000, 0)); // Para paginación
    $totalPages = ceil($totalRecipes / $limit);

    // Obtener información de la categoría seleccionada
    $categoryInfo = null;
    foreach ($categories as $cat) {
        if ($cat['name'] === $selectedCategory) {
            $categoryInfo = $cat;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selectedCategory ? htmlspecialchars($selectedCategory) . ' - ' : ''; ?>Categorías - Recetas Deliciosas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
        <?php if (!$selectedCategory): ?>
        <!-- Vista de todas las categorías -->
        <section class="categories-header">
            <h1>Explorar por Categorías</h1>
            <p>Descubre recetas organizadas por categorías para encontrar exactamente lo que buscas.</p>
        </section>

        <!-- Aquí cambiamos classes="categories-grid" por "category-grid" -->
        <section class="category-grid">
            <?php foreach ($categories as $category): ?>
            <a href="categories.php?category=<?php echo urlencode($category['name']); ?>" class="category-card">
                <div class="category-icon">
                    <?php if (!empty($category['icon_url'])): ?>
                    <img src="<?php echo htmlspecialchars($category['icon_url']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    <?php else: ?>
                    <div class="icon-placeholder"><?php echo substr($category['name'], 0, 1); ?></div>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                <p><?php echo $category['recipe_count']; ?> recetas</p>
            </a>
            <?php endforeach; ?>
        </section>
        <?php else: ?>
        <!-- Vista de recetas de una categoría específica -->
        <section class="category-header">
            <div class="category-info">
                <?php if (!empty($categoryInfo['icon_url'])): ?>
                <div class="category-icon">
                    <img src="<?php echo htmlspecialchars($categoryInfo['icon_url']); ?>" alt="<?php echo htmlspecialchars($selectedCategory); ?>">
                </div>
                <?php endif; ?>
                <div class="category-text">
                    <h1><?php echo htmlspecialchars($selectedCategory); ?></h1>
                    <p><?php echo $totalRecipes; ?> recetas encontradas</p>
                </div>
            </div>
            <a href="categories.php" class="btn btn-outline">Ver todas las categorías</a>
        </section>

        <section class="category-recipes">
            <?php if (empty($recipes)): ?>
            <div class="no-results">
                <h2>No hay recetas en esta categoría</h2>
                <p>Sé el primero en agregar una receta a esta categoría.</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="add-recipe.php" class="btn">Agregar Receta</a>
                <?php else: ?>
                <a href="login.php" class="btn">Iniciar Sesión para Agregar Receta</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="recipe-grid">
                <?php foreach ($recipes as $recipe): ?>
                <div class="recipe-card">
                    <div class="recipe-image">
                        <?php if (!empty($recipe['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                        <?php else: ?>
                        <img src="assets/images/default-recipe.jpg" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="recipe-info">
                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                        <div class="recipe-meta">
                            <span class="rating">
                                <?php echo generateStarRating($recipe['average_rating']); ?>
                            </span>
                        </div>
                        <p><?php echo substr(htmlspecialchars($recipe['description']), 0, 100) . '...'; ?></p>
                        <a href="recipe-details.php?id=<?php echo $recipe['_id']; ?>" class="btn">Ver Receta</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?category=<?php echo urlencode($selectedCategory); ?>&page=<?php echo $page - 1; ?>" class="pagination-link">&laquo; Anterior</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?category=<?php echo urlencode($selectedCategory); ?>&page=<?php echo $i; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                <a href="?category=<?php echo urlencode($selectedCategory); ?>&page=<?php echo $page + 1; ?>" class="pagination-link">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
