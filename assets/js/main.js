/**
 * Funcionalidad principal del sitio
 */
document.addEventListener('DOMContentLoaded', function() {
    // Menú móvil
    setupMobileMenu();
    
    // Notificaciones
    setupNotifications();
    
    // Búsqueda
    setupSearch();
    
    // Modales
    setupModals();
    
    // Tabs
    setupTabs();
});

/**
 * Configuración del menú móvil
 */
function setupMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.getElementById('navLinks');
    
    if (mobileMenuToggle && navLinks) {
        mobileMenuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            
            // Cambiar icono del botón
            const icon = mobileMenuToggle.querySelector('span');
            if (icon) {
                icon.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
            }
        });
        
        // Cerrar menú al hacer clic en un enlace
        const menuLinks = navLinks.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('span');
                if (icon) {
                    icon.textContent = '☰';
                }
            });
        });
    }
}

/**
 * Configuración del sistema de notificaciones
 */
function setupNotifications() {
    // Crear contenedor de notificaciones si no existe
    let notificationsContainer = document.getElementById('notifications-container');
    if (!notificationsContainer) {
        notificationsContainer = document.createElement('div');
        notificationsContainer.id = 'notifications-container';
        document.body.appendChild(notificationsContainer);
    }
    
    // Exponer función global para mostrar notificaciones
    window.showNotification = function(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        notificationsContainer.appendChild(notification);
        
        // Mostrar notificación con animación
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Ocultar y eliminar después del tiempo especificado
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, duration);
    };
    
    // Verificar si hay mensajes de notificación en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type') || 'info';
    
    if (message) {
        showNotification(decodeURIComponent(message), type);
        
        // Limpiar parámetros de la URL
        const url = new URL(window.location);
        url.searchParams.delete('message');
        url.searchParams.delete('type');
        window.history.replaceState({}, '', url);
    }
}

/**
 * Configuración de la funcionalidad de búsqueda
 */
function setupSearch() {
    const searchButton = document.getElementById('search-button');
    const searchInput = document.getElementById('search-input');
    
    if (searchButton && searchInput) {
        searchButton.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query) {
                window.location.href = `recipes.php?search=${encodeURIComponent(query)}`;
            }
        });
        
        // Permitir búsqueda con Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = `recipes.php?search=${encodeURIComponent(query)}`;
                }
            }
        });
    }
}

/**
 * Configuración de modales
 */
function setupModals() {
    // Obtener todos los modales
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        // Botones para cerrar el modal
        const closeButtons = modal.querySelectorAll('.close-modal, [data-close-modal]');
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        // Cerrar modal al hacer clic fuera del contenido
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Botones para abrir modales
    const modalTriggers = document.querySelectorAll('[data-modal]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.style.display = 'block';
            }
        });
    });
}

/**
 * Configuración de tabs
 */
function setupTabs() {
    const tabButtons = document.querySelectorAll('.tab-button');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            const tabContainer = this.closest('.profile-tabs, .tabs-container');
            
            if (tabContainer) {
                // Desactivar todos los tabs
                const allButtons = tabContainer.querySelectorAll('.tab-button');
                const allPanes = document.querySelectorAll('.tab-pane');
                
                allButtons.forEach(btn => btn.classList.remove('active'));
                allPanes.forEach(pane => pane.classList.remove('active'));
                
                // Activar el tab seleccionado
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            }
        });
    });
}

/**
 * Función para confirmar acciones
 * @param {string} message - Mensaje de confirmación
 * @param {Function} callback - Función a ejecutar si se confirma
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Función para formatear fechas
 * @param {string|Date} date - Fecha a formatear
 * @param {string} format - Formato deseado (short, medium, long)
 * @returns {string} Fecha formateada
 */
function formatDate(date, format = 'medium') {
    const dateObj = date instanceof Date ? date : new Date(date);
    
    if (isNaN(dateObj.getTime())) {
        return 'Fecha inválida';
    }
    
    const options = {
        short: { day: '2-digit', month: '2-digit', year: 'numeric' },
        medium: { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' },
        long: { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }
    };
    
    return dateObj.toLocaleDateString('es-ES', options[format] || options.medium);
}

/**
 * Función para validar formularios
 * @param {HTMLFormElement} form - Formulario a validar
 * @param {Object} rules - Reglas de validación
 * @returns {boolean} True si el formulario es válido
 */
function validateForm(form, rules) {
    let isValid = true;
    const errors = {};
    
    // Recorrer cada regla
    for (const field in rules) {
        const input = form.elements[field];
        const fieldRules = rules[field];
        const value = input.value.trim();
        
        // Verificar cada regla para el campo
        for (const rule of fieldRules) {
            switch (rule.type) {
                case 'required':
                    if (value === '') {
                        errors[field] = rule.message || 'Este campo es obligatorio';
                        isValid = false;
                    }
                    break;
                    
                case 'email':
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (value !== '' && !emailRegex.test(value)) {
                        errors[field] = rule.message || 'Email inválido';
                        isValid = false;
                    }
                    break;
                    
                case 'minLength':
                    if (value !== '' && value.length < rule.value) {
                        errors[field] = rule.message || `Debe tener al menos ${rule.value} caracteres`;
                        isValid = false;
                    }
                    break;
                    
                case 'maxLength':
                    if (value !== '' && value.length > rule.value) {
                        errors[field] = rule.message || `Debe tener máximo ${rule.value} caracteres`;
                        isValid = false;
                    }
                    break;
                    
                case 'pattern':
                    if (value !== '' && !rule.pattern.test(value)) {
                        errors[field] = rule.message || 'Formato inválido';
                        isValid = false;
                    }
                    break;
                    
                case 'match':
                    const matchInput = form.elements[rule.field];
                    if (matchInput && value !== matchInput.value) {
                        errors[field] = rule.message || 'Los valores no coinciden';
                        isValid = false;
                    }
                    break;
            }
            
            // Si ya hay un error para este campo, pasar al siguiente
            if (errors[field]) break;
        }
        
        // Mostrar u ocultar mensaje de error
        const errorElement = document.getElementById(`${field}Error`);
        if (errorElement) {
            errorElement.textContent = errors[field] || '';
            
            // Agregar o quitar clase de error
            if (errors[field]) {
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');
            } else {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            }
        }
    }
    
    return isValid;
}