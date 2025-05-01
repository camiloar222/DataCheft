/**
 * Funcionalidad de autenticación
 */
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de inicio de sesión
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        setupLoginForm(loginForm);
    }
    
    // Formulario de registro
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        setupRegisterForm(registerForm);
    }
    
    // Botón de cierre de sesión
    const logoutButton = document.getElementById('logoutButton');
    if (logoutButton) {
        setupLogout(logoutButton);
    }
});

/**
 * Configuración del formulario de inicio de sesión
 * @param {HTMLFormElement} form - Formulario de inicio de sesión
 */
function setupLoginForm(form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Validar formulario
        const isValid = validateForm(form, {
            email: [
                { type: 'required', message: 'Por favor, ingresa tu email' },
                { type: 'email', message: 'Por favor, ingresa un email válido' }
            ],
            password: [
                { type: 'required', message: 'Por favor, ingresa tu contraseña' }
            ]
        });
        
        if (isValid) {
            // Mostrar indicador de carga
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Iniciando sesión...';
            
            // Preparar datos
            const formData = new FormData(form);
            formData.append('action', 'login');
            
            // Enviar solicitud
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    showNotification(data.message, 'success');
                    
                    // Redirigir
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1000);
                } else {
                    // Mostrar errores
                    if (data.errors) {
                        for (const field in data.errors) {
                            const errorElement = document.getElementById(`${field}Error`);
                            if (errorElement) {
                                errorElement.textContent = data.errors[field];
                                
                                // Marcar campo como inválido
                                const input = form.elements[field];
                                if (input) {
                                    input.classList.add('is-invalid');
                                }
                            }
                        }
                    }
                    
                    // Mostrar mensaje general de error
                    if (data.errors && data.errors.general) {
                        showNotification(data.errors.general, 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al procesar la solicitud. Por favor, inténtalo de nuevo.', 'error');
            })
            .finally(() => {
                // Restaurar botón
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        }
    });
}

/**
 * Configuración del formulario de registro
 * @param {HTMLFormElement} form - Formulario de registro
 */
function setupRegisterForm(form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Validar formulario
        const isValid = validateForm(form, {
            name: [
                { type: 'required', message: 'Por favor, ingresa tu nombre' }
            ],
            email: [
                { type: 'required', message: 'Por favor, ingresa tu email' },
                { type: 'email', message: 'Por favor, ingresa un email válido' }
            ],
            password: [
                { type: 'required', message: 'Por favor, ingresa una contraseña' },
                { type: 'minLength', value: 8, message: 'La contraseña debe tener al menos 8 caracteres' },
                { 
                    type: 'pattern', 
                    pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/,
                    message: 'La contraseña debe contener al menos una letra mayúscula, una minúscula y un número'
                }
            ],
            confirmPassword: [
                { type: 'required', message: 'Por favor, confirma tu contraseña' },
                { type: 'match', field: 'password', message: 'Las contraseñas no coinciden' }
            ]
        });
        
        if (isValid) {
            // Mostrar indicador de carga
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Registrando...';
            
            // Preparar datos
            const formData = new FormData(form);
            formData.append('action', 'register');
            
            // Enviar solicitud
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    showNotification(data.message, 'success');
                    
                    // Redirigir
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1000);
                } else {
                    // Mostrar errores
                    if (data.errors) {
                        for (const field in data.errors) {
                            const errorElement = document.getElementById(`${field}Error`);
                            if (errorElement) {
                                errorElement.textContent = data.errors[field];
                                
                                // Marcar campo como inválido
                                const input = form.elements[field];
                                if (input) {
                                    input.classList.add('is-invalid');
                                }
                            }
                        }
                    }
                    
                    // Mostrar mensaje general de error
                    if (data.errors && data.errors.general) {
                        showNotification(data.errors.general, 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al procesar la solicitud. Por favor, inténtalo de nuevo.', 'error');
            })
            .finally(() => {
                // Restaurar botón
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        }
    });
}

/**
 * Configuración del botón de cierre de sesión
 * @param {HTMLElement} button - Botón de cierre de sesión
 */
function setupLogout(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        
        // Confirmar cierre de sesión
        confirmAction('¿Estás seguro de que deseas cerrar sesión?', function() {
            // Preparar datos
            const formData = new FormData();
            formData.append('action', 'logout');
            
            // Enviar solicitud
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    showNotification(data.message, 'success');
                    
                    // Redirigir
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1000);
                } else {
                    showNotification('Error al cerrar sesión. Por favor, inténtalo de nuevo.', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al procesar la solicitud. Por favor, inténtalo de nuevo.', 'error');
            });
        });
    });
}

/**
 * Verificar si el usuario está autenticado
 * @returns {boolean} True si el usuario está autenticado
 */
function isAuthenticated() {
    // Verificar si existe la variable de sesión en JavaScript
    return document.body.classList.contains('user-authenticated');
}

/**
 * Redirigir al usuario a la página de inicio de sesión si no está autenticado
 * @param {string} redirectUrl - URL a la que redirigir después del inicio de sesión
 */
function requireAuthentication(redirectUrl = window.location.href) {
    if (!isAuthenticated()) {
        window.location.href = `login.php?redirect=${encodeURIComponent(redirectUrl)}`;
        return false;
    }
    return true;
}