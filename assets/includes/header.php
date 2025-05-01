<header>
    <div class="container">
        <nav class="navbar">
            <a href="index.php" class="logo">Recetas Deliciosas</a>
            
            <div class="mobile-menu-toggle" id="mobileMenuToggle">
                <span>☰</span>
            </div>
            
            <ul class="nav-links" id="navLinks">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="categories.php">Categorías</a></li>
                <li><a href="recipes.php">Explorar Recetas</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="add-recipe.php">Agregar Receta</a></li>
                <?php endif; ?>
            </ul>
            
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="btn btn-outline">Mi Perfil</a>
                <a href="logout.php" class="btn">Cerrar Sesión</a>
                <?php else: ?>
                <a href="login.php" class="btn btn-outline">Iniciar Sesión</a>
                <a href="register.php" class="btn">Registrarse</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<script>
    // Toggle menu en dispositivos móviles
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navLinks = document.getElementById('navLinks');
        
        if (mobileMenuToggle && navLinks) {
            mobileMenuToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
            });
        }
    });
</script>