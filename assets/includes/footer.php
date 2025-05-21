<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-column">
                <h3>DataCheft</h3>
                <p>Tu plataforma para descubrir y compartir recetas increíbles con la comunidad.</p>
            </div>
            
            <div class="footer-column">
                <h3>Enlaces Rápidos</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="categories.php">Categorías</a></li>
                    <li><a href="recipes.php">Explorar Recetas</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="add-recipe.php">Agregar Receta</a></li>
                    <li><a href="profile.php">Mi Perfil</a></li>
                    <?php else: ?>
                    <li><a href="login.php">Iniciar Sesión</a></li>
                    <li><a href="register.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Categorías Populares</h3>
                <ul class="footer-links">
                    <li><a href="categories.php?category=Postres">Postres</a></li>
                    <li><a href="categories.php?category=Platos%20Principales">Platos Principales</a></li>
                    <li><a href="categories.php?category=Ensaladas">Ensaladas</a></li>
                    <li><a href="categories.php?category=Sopas">Sopas</a></li>
                    <li><a href="categories.php?category=Vegano">Vegano</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Contacto</h3>
                <ul class="footer-links">
                    <li><a href="mailto:info@recetasdeliciosas.com">datacheft@gmail.com</a></li>
                    <li><a href="tel:+123456789">01 800 3000 444</a></li>
                    <li><a href="#">Política de Privacidad</a></li>
                    <li><a href="#">Términos y Condiciones</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> DataCheft. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>