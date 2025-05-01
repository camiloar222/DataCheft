<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

// Parámetros de paginación
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$skip = ($page - 1) * $limit;

// Parámetros de búsqueda y filtrado
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Obtener recetas según los filtros
if (!empty($search)) {
    $recipes = searchRecipes($search, $limit, $skip);
    $totalRecipes = count(searchRecipes($search, 1000, 0)); // Para paginación
} elseif (!empty($category)) {
    $recipes = getRecipesByCategory($category, $limit, $skip);
    $totalRecipes = count(getRecipesByCategory($category, 1000, 0)); // Para paginación
} else {
    // Obtener todas las recetas
    global $recipesCollection;
    
    $options = [
        'limit' => $limit,
        'skip' => $skip
    ];
    
    // Ordenar según el parámetro
    switch ($sort) {
        case 'rating':
            $options['sort'] = ['average_rating' => -1];
            break;
        case 'oldest':
            $options['sort'] = ['created_at' => 1];
            break;
        case 'newest':
        default:
            $options['sort'] = ['created_at' => -1];
            break;
    }
    
    $recipes = $recipesCollection->find([], $options)->toArray();
    $totalRecipes = $recipesCollection->countDocuments([]);
}

// Calcular total de páginas
$totalPages = ceil($totalRecipes / $limit);

// Obtener todas las categorías para el filtro
$categories = getAllCategories();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar Recetas - Recetas Deliciosas</title>'
    
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* Estilos específicos para la página de exploración de recetas */
        .recipes-header {
            margin-bottom: 30px;
        }
        
        .recipes-header h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2rem;
            color: var(--text-color);
        }
        
        .search-filter-container {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .search-input-container {
            display: flex;
            flex: 1;
        }
        
        .search-input-container input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            font-size: 1rem;
        }
        
        .search-button {
            padding: 12px 20px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            cursor: pointer;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .search-button:hover {
            background-color: #ff5252;
        }
        
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--medium-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            background-color: var(--white);
        }
        
        .results-count {
            margin-bottom: 20px;
            color: var(--dark-gray);
        }
        
        .no-results {
            text-align: center;
            padding: 50px 20px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }
        
        .no-results h2 {
            margin-bottom: 10px;
            color: var(--text-color);
        }
        
        .no-results p {
            color: var(--dark-gray);
        }
        
        /* Estilos para la paginación */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .pagination-link {
            display: inline-block;
            padding: 8px 12px;
            background-color: var(--white);
            color: var(--text-color);
            border-radius: var(--border-radius);
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid var(--medium-gray);
        }
        
        .pagination-link:hover {
            background-color: var(--light-gray);
        }
        
        .pagination-link.active {
            background-color: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }
        
        /* Responsive */
        @media (min-width: 768px) {
            .search-form {
                flex-direction: row;
                align-items: flex-end;
            }
            
            .search-input-container {
                flex: 2;
            }
            
            .filter-container {
                flex: 1;
            }
        }
        
        @media (max-width: 767px) {
            .search-input-container {
                margin-bottom: 15px;
            }
            
            .filter-group {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <section class="recipes-header">
            <h1>Explorar Recetas</h1>
            
            <div class="search-filter-container">
                <form action="recipes.php" method="GET" class="search-form">
                    <div class="search-input-container">
                        <input type="text" name="search" placeholder="Buscar recetas..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-button">Buscar</button>
                    </div>
                    
                    <div class="filter-container">
                        <div class="filter-group">
                            <label for="category-filter">Categoría:</label>
                            <select name="category" id="category-filter" onchange="this.form.submit()">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $category === $cat['name'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="sort-filter">Ordenar por:</label>
                            <select name="sort" id="sort-filter" onchange="this.form.submit()">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Más recientes</option>
                                <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Más antiguas</option>
                                <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Mejor valoradas</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </section>
        
        <section class="recipes-results">
            <?php if (empty($recipes)): ?>
            <div class="no-results">
                <h2>No se encontraron recetas</h2>
                <p>Intenta con otros términos de búsqueda o filtros.</p>
            </div>
            <?php else: ?>
            <div class="results-count">
                <p>Se encontraron <?php echo $totalRecipes; ?> recetas</p>
            </div>
            
            <div class="recipe-grid">
                <?php foreach ($recipes as $recipe): ?>
                <div class="recipe-card">
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
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($sort) ? '&sort=' . urlencode($sort) : ''; ?>" class="pagination-link">&laquo; Anterior</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($sort) ? '&sort=' . urlencode($sort) : ''; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($sort) ? '&sort=' . urlencode($sort) : ''; ?>" class="pagination-link">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>