<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
session_start();

$recipeId = $_GET['id'] ?? null;
if (!$recipeId) {
    header('Location: index.php');
    exit;
}

$recipe = getRecipeById($recipeId);
if (!$recipe) {
    header('Location: index.php');
    exit;
}

$comments = getRecipeComments($recipeId);

$userRating = 0;
if (isset($_SESSION['user_id'])) {
    global $ratingsCollection;
    $existingRating = $ratingsCollection->findOne([
        'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
        'recipe_id' => new MongoDB\BSON\ObjectId($recipeId)
    ]);
    if ($existingRating) {
        $userRating = $existingRating['rating'];
    }
}

$author = getUserById($recipe['user_id']);

// Generar estrellas en base al promedio (versión SVG)
function renderAverageStars($average) {
    $starsHtml = '';
    for ($i = 1; $i <= 5; $i++) {
        $fill = ($i <= $average) ? 'gold' : '#ccc';
        $starsHtml .= '<svg class="rating-star" style="fill: '.$fill.'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
    }
    return $starsHtml;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - Recetas Deliciosas</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .comment-header i {
            color: #444;
            margin-right: 6px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.5s ease-in-out;
        }

        .rating-star {
            width: 28px;
            height: 28px;
            cursor: pointer;
            transition: transform 0.2s ease, fill 0.3s ease;
            fill: #ccc;
        }

        .rating-star.active {
            fill: gold;
        }

        .rating-star.hover {
            fill: #ffdd57;
            transform: scale(1.2);
        }

        .rating-star.pop {
            animation: pop-star 0.3s ease;
        }

        @keyframes pop-star {
            0%   { transform: scale(1.4); }
            50%  { transform: scale(1.1); }
            100% { transform: scale(1.2); }
        }

        .rating-container {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .rating-container .stars {
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="recipe-details">
        <div class="recipe-header">
            <h1><?php echo htmlspecialchars($recipe['title']); ?></h1>

            <div class="recipe-meta">
                <span class="category"><?php echo htmlspecialchars($recipe['category']); ?></span>
                <div class="rating-container">
                    <span id="average-rating"><?php echo number_format($recipe['average_rating'], 1); ?></span>
                    <div class="stars">
                        <?php echo renderAverageStars($recipe['average_rating']); ?>
                    </div>
                    <span class="ratings-count">(<?php echo $recipe['ratings_count']; ?> calificaciones)</span>
                </div>
            </div>

            <div class="recipe-author">
                <span>Por: <?php echo htmlspecialchars($author['name'] ?? 'Usuario desconocido'); ?></span>
                <span class="recipe-date">
                    <?php 
                    $date = $recipe['created_at']->toDateTime();
                    echo $date->format('d/m/Y'); 
                    ?>
                </span>
            </div>
        </div>

        <?php if (!empty($recipe['image_url'])): ?>
        <div class="recipe-image-large">
            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
        </div>
        <?php endif; ?>

        <div class="recipe-description">
            <h2>Descripción</h2>
            <p><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
        </div>

        <div class="recipe-content">
            <div class="recipe-ingredients">
                <h2>Ingredientes</h2>
                <ul>
                    <?php 
                    $ingredients = explode("\n", $recipe['ingredients']);
                    foreach ($ingredients as $ingredient): 
                        if (trim($ingredient) !== ''):
                    ?>
                    <li><?php echo htmlspecialchars(trim($ingredient)); ?></li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>

            <div class="recipe-instructions">
                <h2>Instrucciones</h2>
                <ol>
                    <?php 
                    $instructions = explode("\n", $recipe['instructions']);
                    foreach ($instructions as $instruction): 
                        if (trim($instruction) !== ''):
                    ?>
                    <li><?php echo htmlspecialchars(trim($instruction)); ?></li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ol>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-rating">
            <h3>¿Qué te pareció esta receta?</h3>
            <div class="rating-container" data-recipe-id="<?php echo $recipe['_id']; ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <svg class="rating-star <?php echo $i <= $userRating ? 'active' : ''; ?>" data-value="<?php echo $i; ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                </svg>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="comments-section">
            <h2>Comentarios (<?php echo count($comments); ?>)</h2>

            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="comment-form">
                <form id="commentForm" data-recipe-id="<?php echo $recipe['_id']; ?>">
                    <div class="form-group">
                        <label for="commentText">Deja tu comentario</label>
                        <textarea id="commentText" class="form-control" rows="4" placeholder="Escribe tu comentario aquí..."></textarea>
                    </div>
                    <button type="submit" class="btn">Publicar comentario</button>
                </form>
            </div>
            <?php else: ?>
            <div class="login-to-comment">
                <p>Debes <a href="login.php">iniciar sesión</a> para dejar un comentario.</p>
            </div>
            <?php endif; ?>

            <div class="comments-list">
                <?php if (empty($comments)): ?>
                <div class="no-comments">
                    <p>No hay comentarios aún. ¡Sé el primero en comentar!</p>
                </div>
                <?php else: ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment fade-in">
                        <div class="comment-header">
                            <span class="comment-author">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($comment['user_name']); ?>
                            </span>
                            <span class="comment-date"><?php echo $comment['formatted_date']; ?></span>
                        </div>
                        <div class="comment-body">
                            <p><?php echo nl2br(htmlspecialchars($comment['text'])); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
<script src="js/recipes.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('.user-rating .rating-star');
    const container = document.querySelector('.user-rating .rating-container');
    const averageRatingText = document.getElementById('average-rating');
    const starsContainer = document.querySelector('.recipe-meta .stars');
    const ratingsCountSpan = document.querySelector('.ratings-count');

    if (!container || stars.length === 0) return;

    const recipeId = container.getAttribute('data-recipe-id');

    let currentRating = [...stars].filter(star => star.classList.contains('active')).length;

    const renderStars = (rating) => {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('active', 'pop');
            } else {
                star.classList.remove('active');
            }
            setTimeout(() => star.classList.remove('pop'), 300);
        });
    };

    stars.forEach(star => {
        star.addEventListener('mouseenter', () => {
            const hoverValue = parseInt(star.dataset.value);
            stars.forEach((s, i) => {
                s.classList.toggle('hover', i < hoverValue);
            });
        });

        star.addEventListener('mouseleave', () => {
            stars.forEach(s => s.classList.remove('hover'));
        });

        star.addEventListener('click', () => {
            const rating = parseInt(star.dataset.value);
            if (!recipeId) return;

            // CORREGIDO: antes decía 'ratings.php'
            fetch('api/ratings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ recipe_id: recipeId, rating })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentRating = rating;
                    renderStars(currentRating);

                    // Actualiza el promedio y la cantidad de ratings
                    averageRatingText.textContent = data.average_rating.toFixed(1);

                    // Renderiza las estrellas promedio nuevamente
                    if (starsContainer) {
                        let newStarsHtml = '';
                        for (let i = 1; i <= 5; i++) {
                            const fill = i <= Math.round(data.average_rating) ? 'gold' : '#ccc';
                            newStarsHtml += `
                                <svg class="rating-star" style="fill: ${fill}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                </svg>`;
                        }
                        starsContainer.innerHTML = newStarsHtml;
                    }

                    if (ratingsCountSpan) {
                        ratingsCountSpan.textContent = `(${data.ratings_count} calificaciones)`;
                    }
                } else {
                    alert(data.message || 'Error al calificar');
                }
            })
            .catch(err => {
                console.error('Error al calificar:', err);
                alert('Ocurrió un error. Intenta más tarde.');
            });
        });
    });
});
</script>
