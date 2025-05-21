<?php
// Funciones de utilidad para la plataforma

// Función para verificar si un email ya existe
function emailExists($email) {
    global $usersCollection;
    
    $user = $usersCollection->findOne(['email' => $email]);
    return $user !== null;
}

// Función para registrar un nuevo usuario
function registerUser($name, $email, $password) {
    global $usersCollection;
    
    // Hashear la contraseña
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Crear documento de usuario
    $result = $usersCollection->insertOne([
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    
    return $result->getInsertedCount() === 1;
}

// Función para obtener un usuario por email
function getUserByEmail($email) {
    global $usersCollection;
    
    return $usersCollection->findOne(['email' => $email]);
}

// Función para obtener un usuario por ID
function getUserById($id) {
    global $usersCollection;
    
    return $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
}

// Función para verificar credenciales de inicio de sesión
function verifyLogin($email, $password) {
    $user = getUserByEmail($email);
    
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    
    return false;
}

// Función para obtener recetas destacadas
function getFeaturedRecipes($limit = 6) {
    global $recipesCollection;
    
    $options = [
        'sort' => ['average_rating' => -1],
        'limit' => $limit
    ];
    
    $recipes = $recipesCollection->find([], $options);
    return $recipes->toArray();
}

// Función para obtener recetas por categoría
function getRecipesByCategory($category, $limit = 12, $skip = 0) {
    global $recipesCollection;
    
    $options = [
        'sort' => ['created_at' => -1],
        'limit' => $limit,
        'skip' => $skip
    ];
    
    $recipes = $recipesCollection->find(['category' => $category], $options);
    return $recipes->toArray();
}

// Función para obtener todas las categorías
function getAllCategories() {
    global $categoriesCollection, $recipesCollection;

    try {
        // Obtener todas las categorías
        $categories = $categoriesCollection->find()->toArray();

        // Contar recetas por categoría
        $counts = $recipesCollection->aggregate([
            [
                '$group' => [
                    '_id' => '$category',
                    'recipe_count' => ['$sum' => 1]
                ]
            ]
        ])->toArray();

        // Convertir conteos a mapa
        $countsMap = [];
        foreach ($counts as $entry) {
            $countsMap[$entry['_id']] = $entry['recipe_count'];
        }

        // Añadir los conteos a cada categoría
        foreach ($categories as &$category) {
            $name = $category['name'];
            $category['recipe_count'] = $countsMap[$name] ?? 0;
        }

        return $categories;

    } catch (Exception $e) {
        // Si algo falla, mostrar las categorías sin contar recetas
        error_log("Error obteniendo categorías con conteo: " . $e->getMessage());
        $categories = $categoriesCollection->find()->toArray();

        // Asignar 0 recetas si no se pudo contar
        foreach ($categories as &$category) {
            $category['recipe_count'] = 0;
        }

        return $categories;
    }
}



// Función para obtener una receta por ID
function getRecipeById($recipeId) {
    global $recipesCollection;
    
    try {
        return $recipesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($recipeId)]);
    } catch (Exception $e) {
        error_log('Error al obtener receta: ' . $e->getMessage());
        return null;
    }
}

// Función para crear una nueva receta
function createRecipe($userId, $title, $category, $description, $ingredients, $instructions, $imageUrl = null) {
    global $recipesCollection;

    try {
        $result = $recipesCollection->insertOne([
            'user_id' => new MongoDB\BSON\ObjectId($userId),
            'title' => $title,
            'category' => $category,
            'description' => $description,
            'ingredients' => $ingredients,
            'instructions' => $instructions,
            'image_url' => $imageUrl ?? null,
            'average_rating' => 0.0, // aseguramos tipo double
            'ratings_count' => 0,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);

        return $result->getInsertedCount() === 1 ? $result->getInsertedId() : false;

    } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
        echo "<h3>Error de validación al insertar en MongoDB</h3>";
        echo "<pre>";
        var_dump($e->getWriteResult()->getWriteErrors());
        echo "</pre>";
        exit;
    } catch (Exception $e) {
        echo "<h3>Error general al insertar la receta:</h3>";
        echo "<pre>";
        echo $e->getMessage();
        echo "</pre>";
        exit;
    }
}



// Función para actualizar una receta
function updateRecipe($recipeId, $title, $category, $description, $ingredients, $instructions, $imageUrl = null) {
    global $recipesCollection;
    
    $updateData = [
        'title' => $title,
        'category' => $category,
        'description' => $description,
        'ingredients' => $ingredients,
        'instructions' => $instructions,
        'updated_at' => new MongoDB\BSON\UTCDateTime()
    ];
    
    if ($imageUrl !== null) {
        $updateData['image_url'] = $imageUrl;
    }
    
    $result = $recipesCollection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($recipeId)],
        ['$set' => $updateData]
    );
    
    return $result->getModifiedCount() === 1;
}

// Función para eliminar una receta
function deleteRecipe($recipeId) {
    global $recipesCollection, $commentsCollection, $ratingsCollection;
    
    // Eliminar comentarios y calificaciones asociados
    $commentsCollection->deleteMany(['recipe_id' => new MongoDB\BSON\ObjectId($recipeId)]);
    $ratingsCollection->deleteMany(['recipe_id' => new MongoDB\BSON\ObjectId($recipeId)]);
    
    // Eliminar la receta
    $result = $recipesCollection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($recipeId)]);
    
    return $result->getDeletedCount() === 1;
}

// Función para obtener comentarios de una receta
function getRecipeComments($recipeId, $limit = 10, $skip = 0) {
    global $commentsCollection, $usersCollection;
    
    $options = [
        'sort' => ['created_at' => -1],
        'limit' => $limit,
        'skip' => $skip
    ];
    
    $comments = $commentsCollection->find(['recipe_id' => new MongoDB\BSON\ObjectId($recipeId)], $options);
    $commentsArray = $comments->toArray();
    
    // Agregar información del usuario a cada comentario
    foreach ($commentsArray as &$comment) {
        $user = $usersCollection->findOne(['_id' => $comment['user_id']]);
        $comment['user_name'] = $user ? $user['name'] : 'Usuario desconocido';
        
        // Formatear fecha
        $date = $comment['created_at']->toDateTime();
        $comment['formatted_date'] = $date->format('d/m/Y H:i');
    }
    
    return $commentsArray;
}

// Función para agregar un comentario
function addComment($userId, $recipeId, $text) {
    global $commentsCollection;
    
    $result = $commentsCollection->insertOne([
        'user_id' => new MongoDB\BSON\ObjectId($userId),
        'recipe_id' => new MongoDB\BSON\ObjectId($recipeId),
        'text' => $text,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    
    if ($result->getInsertedCount() === 1) {
        $commentId = $result->getInsertedId();
        $comment = $commentsCollection->findOne(['_id' => $commentId]);
        
        // Agregar información del usuario
        $user = getUserById($userId);
        $comment['user_name'] = $user ? $user['name'] : 'Usuario desconocido';
        
        // Formatear fecha
        $date = $comment['created_at']->toDateTime();
        $comment['formatted_date'] = $date->format('d/m/Y H:i');
        
        return $comment;
    }
    
    return false;
}

// Función para calificar una receta
function rateRecipe($userId, $recipeId, $rating) {
    global $ratingsCollection, $recipesCollection;

    $userObjectId = new MongoDB\BSON\ObjectId($userId);
    $recipeObjectId = new MongoDB\BSON\ObjectId($recipeId);

    // Verificar si el usuario ya calificó esta receta
    $existingRating = $ratingsCollection->findOne([
        'user_id' => $userObjectId,
        'recipe_id' => $recipeObjectId
    ]);

    if ($existingRating) {
        // Actualizar calificación existente
        $ratingsCollection->updateOne(
            ['_id' => $existingRating['_id']],
            ['$set' => [
                'rating' => (int)$rating,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]]
        );
    } else {
        // Crear nueva calificación
        $ratingsCollection->insertOne([
            'user_id' => $userObjectId,
            'recipe_id' => $recipeObjectId,
            'rating' => (int)$rating,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]);
    }

    // Obtener todas las calificaciones y calcular promedio
    $ratings = $ratingsCollection->find(['recipe_id' => $recipeObjectId]);
    $ratingsArray = $ratings->toArray();
    $ratingsCount = count($ratingsArray);

    $ratingSum = array_sum(array_column($ratingsArray, 'rating'));
    $averageRating = $ratingsCount > 0 ? $ratingSum / $ratingsCount : 0;

    // ✅ Redondear promedio antes de guardar y devolver
    $averageRating = round($averageRating, 1);

    // Guardar el promedio en la colección de recetas
    $recipesCollection->updateOne(
        ['_id' => $recipeObjectId],
        ['$set' => [
            'average_rating' => $averageRating,
            'ratings_count' => $ratingsCount
        ]]
    );

    return [
        'average' => $averageRating,
        'count' => $ratingsCount
    ];
}



// Función para generar estrellas HTML para calificación
function generateStarRating($rating) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    
    $html = '';
    
    // Estrellas completas
    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<span class="star full">★</span>';
    }
    
    // Media estrella
    if ($halfStar) {
        $html .= '<span class="star half">★</span>';
    }
    
    // Estrellas vacías
    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<span class="star empty">☆</span>';
    }
    
    return $html;
}

// Función para buscar recetas
function searchRecipes($query, $limit = 12, $skip = 0) {
    global $recipesCollection;
    
    $options = [
        'sort' => ['average_rating' => -1],
        'limit' => $limit,
        'skip' => $skip
    ];
    
    // Crear índice de texto si no existe
    $recipesCollection->createIndex(['title' => 'text', 'description' => 'text']);
    
    $recipes = $recipesCollection->find(
        ['$text' => ['$search' => $query]],
        $options
    );
    
    return $recipes->toArray();
}