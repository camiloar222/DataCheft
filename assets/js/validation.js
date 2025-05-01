// Validación de formularios
document.addEventListener('DOMContentLoaded', function() {
    // Obtener todos los formularios que necesitan validación
    const forms = document.querySelectorAll('.needs-validation');
    
    // Función para validar email
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Función para validar contraseña
    function isValidPassword(password) {
        // Al menos 8 caracteres, una letra mayúscula, una minúscula y un número
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return passwordRegex.test(password);
    }
    
    // Validar formulario de registro
    if (document.getElementById('registerForm')) {
        const registerForm = document.getElementById('registerForm');
        
        registerForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validar nombre
            const nameInput = document.getElementById('name');
            const nameError = document.getElementById('nameError');
            
            if (nameInput.value.trim() === '') {
                nameError.textContent = 'Por favor, ingresa tu nombre';
                nameInput.classList.add('is-invalid');
                isValid = false;
            } else {
                nameError.textContent = '';
                nameInput.classList.remove('is-invalid');
                nameInput.classList.add('is-valid');
            }
            
            // Validar email
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            
            if (emailInput.value.trim() === '') {
                emailError.textContent = 'Por favor, ingresa tu email';
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                emailError.textContent = 'Por favor, ingresa un email válido';
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else {
                emailError.textContent = '';
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
            }
            
            // Validar contraseña
            const passwordInput = document.getElementById('password');
            const passwordError = document.getElementById('passwordError');
            
            if (passwordInput.value === '') {
                passwordError.textContent = 'Por favor, ingresa una contraseña';
                passwordInput.classList.add('is-invalid');
                isValid = false;
            } else if (!isValidPassword(passwordInput.value)) {
                passwordError.textContent = 'La contraseña debe tener al menos 8 caracteres, una letra mayúscula, una minúscula y un número';
                passwordInput.classList.add('is-invalid');
                isValid = false;
            } else {
                passwordError.textContent = '';
                passwordInput.classList.remove('is-invalid');
                passwordInput.classList.add('is-valid');
            }
            
            // Validar confirmación de contraseña
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const confirmPasswordError = document.getElementById('confirmPasswordError');
            
            if (confirmPasswordInput.value === '') {
                confirmPasswordError.textContent = 'Por favor, confirma tu contraseña';
                confirmPasswordInput.classList.add('is-invalid');
                isValid = false;
            } else if (confirmPasswordInput.value !== passwordInput.value) {
                confirmPasswordError.textContent = 'Las contraseñas no coinciden';
                confirmPasswordInput.classList.add('is-invalid');
                isValid = false;
            } else {
                confirmPasswordError.textContent = '';
                confirmPasswordInput.classList.remove('is-invalid');
                confirmPasswordInput.classList.add('is-valid');
            }
            
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }
    
    // Validar formulario de inicio de sesión
    if (document.getElementById('loginForm')) {
        const loginForm = document.getElementById('loginForm');
        
        loginForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validar email
            const emailInput = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            
            if (emailInput.value.trim() === '') {
                emailError.textContent = 'Por favor, ingresa tu email';
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else if (!isValidEmail(emailInput.value.trim())) {
                emailError.textContent = 'Por favor, ingresa un email válido';
                emailInput.classList.add('is-invalid');
                isValid = false;
            } else {
                emailError.textContent = '';
                emailInput.classList.remove('is-invalid');
                emailInput.classList.add('is-valid');
            }
            
            // Validar contraseña
            const passwordInput = document.getElementById('password');
            const passwordError = document.getElementById('passwordError');
            
            if (passwordInput.value === '') {
                passwordError.textContent = 'Por favor, ingresa tu contraseña';
                passwordInput.classList.add('is-invalid');
                isValid = false;
            } else {
                passwordError.textContent = '';
                passwordInput.classList.remove('is-invalid');
                passwordInput.classList.add('is-valid');
            }
            
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }
    
    // Validar formulario de agregar receta
    if (document.getElementById('recipeForm')) {
        const recipeForm = document.getElementById('recipeForm');
        
        recipeForm.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Validar título
            const titleInput = document.getElementById('title');
            const titleError = document.getElementById('titleError');
            
            if (titleInput.value.trim() === '') {
                titleError.textContent = 'Por favor, ingresa un título para la receta';
                titleInput.classList.add('is-invalid');
                isValid = false;
            } else {
                titleError.textContent = '';
                titleInput.classList.remove('is-invalid');
                titleInput.classList.add('is-valid');
            }
            
            // Validar categoría
            const categoryInput = document.getElementById('category');
            const categoryError = document.getElementById('categoryError');
            
            if (categoryInput.value === '') {
                categoryError.textContent = 'Por favor, selecciona una categoría';
                categoryInput.classList.add('is-invalid');
                isValid = false;
            } else {
                categoryError.textContent = '';
                categoryInput.classList.remove('is-invalid');
                categoryInput.classList.add('is-valid');
            }
            
            // Validar descripción
            const descriptionInput = document.getElementById('description');
            const descriptionError = document.getElementById('descriptionError');
            
            if (descriptionInput.value.trim() === '') {
                descriptionError.textContent = 'Por favor, ingresa una descripción';
                descriptionInput.classList.add('is-invalid');
                isValid = false;
            } else {
                descriptionError.textContent = '';
                descriptionInput.classList.remove('is-invalid');
                descriptionInput.classList.add('is-valid');
            }
            
            // Validar ingredientes
            const ingredientsInput = document.getElementById('ingredients');
            const ingredientsError = document.getElementById('ingredientsError');
            
            if (ingredientsInput.value.trim() === '') {
                ingredientsError.textContent = 'Por favor, ingresa los ingredientes';
                ingredientsInput.classList.add('is-invalid');
                isValid = false;
            } else {
                ingredientsError.textContent = '';
                ingredientsInput.classList.remove('is-invalid');
                ingredientsInput.classList.add('is-valid');
            }
            
            // Validar instrucciones
            const instructionsInput = document.getElementById('instructions');
            const instructionsError = document.getElementById('instructionsError');
            
            if (instructionsInput.value.trim() === '') {
                instructionsError.textContent = 'Por favor, ingresa las instrucciones';
                instructionsInput.classList.add('is-invalid');
                isValid = false;
            } else {
                instructionsError.textContent = '';
                instructionsInput.classList.remove('is-invalid');
                instructionsInput.classList.add('is-valid');
            }
            
            if (!isValid) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }
});