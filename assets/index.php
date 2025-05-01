<?php

require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

// Obtener recetas destacadas
$featuredRecipes = getFeaturedRecipes();
// Obtener categorías
$categories = getAllCategories();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recetas Deliciosas - Tu plataforma de recetas</title>    
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <section class="hero">
            <div class="hero-content">
                <h1>Descubre recetas increíbles</h1>
                <p>Explora miles de recetas compartidas por nuestra comunidad</p>
                <div class="search-container">
                    <input type="text" id="search-input" placeholder="Buscar recetas...">
                    <button id="search-button">Buscar</button>
                </div>
            </div>
        </section>

        <section class="featured-recipes">
            <h2>Recetas Destacadas</h2>
            <div class="recipe-grid">
                <?php foreach ($featuredRecipes as $recipe): ?>
                <div class="recipe-card">
                    <div class="recipe-image">
                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
                    </div>
                    <div class="recipe-info">
                        <h3><?php echo htmlspecialchars($recipe['title']); ?></h3>
                        <div class="recipe-meta">
                            <span class="category"><?php echo htmlspecialchars($recipe['category']); ?></span>
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
        </section>

        <section class="categories">
            <h2>Explora por Categorías</h2>
            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                <a href="categories.php?category=<?php echo urlencode($category['name']); ?>" class="category-card">
                    <div class="category-icon">
                        <img src="<?php echo htmlspecialchars($category['icon_url']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                    </div>
                    <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                    <p><?php echo htmlspecialchars($category['recipe_count']); ?> recetas</p>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>