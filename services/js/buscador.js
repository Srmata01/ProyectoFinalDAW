/**
 * Buscador reutilizable con AJAX
 * Este script proporciona funcionalidad de búsqueda para cualquier página del sitio.
 * Soporta búsqueda en tiempo real y autocompletado.
 */
class Buscador {
    constructor(options) {
        // Opciones configurables
        this.options = {
            inputSelector: '.search-input', // Selector del input de búsqueda
            resultsSelector: '#resultados-busqueda', // Donde se mostrarán los resultados
            searchUrl: '/services/php/buscar.php', // URL del script de búsqueda
            autocompleteUrl: '/services/php/autocompletar.php', // URL del script de autocompletado
            tipo: 'general', // Tipo de búsqueda: 'servicios', 'usuarios', etc.
            minChars: 2, // Mínimo de caracteres para iniciar búsqueda
            delay: 300, // Retardo en milisegundos para la búsqueda
            submitButton: null, // Botón de envío (opcional)
            onResultsLoaded: null, // Callback cuando se cargan los resultados
            ...options
        };

        // Elementos DOM
        this.inputElement = document.querySelector(this.options.inputSelector);
        this.resultsElement = document.querySelector(this.options.resultsSelector);
        this.submitButton = this.options.submitButton ? document.querySelector(this.options.submitButton) : null;
        
        // Variables de control
        this.timer = null;
        this.autocompleteContainer = null;
        
        // Inicializar
        if (this.inputElement) {
            this.init();
        } else {
            console.error('No se encontró el elemento de búsqueda especificado');
        }
    }

    init() {
        // Crear contenedor para el autocompletado si no existe
        if (!document.getElementById('autocomplete-container')) {
            this.autocompleteContainer = document.createElement('div');
            this.autocompleteContainer.id = 'autocomplete-container';
            this.autocompleteContainer.className = 'autocomplete-container';
            this.autocompleteContainer.style.display = 'none';
            this.inputElement.parentNode.insertBefore(this.autocompleteContainer, this.inputElement.nextSibling);
        } else {
            this.autocompleteContainer = document.getElementById('autocomplete-container');
        }

        // Eventos de búsqueda
        this.inputElement.addEventListener('input', this.handleInput.bind(this));
        this.inputElement.addEventListener('focus', this.handleFocus.bind(this));
        document.addEventListener('click', this.handleDocumentClick.bind(this));

        // Evento para el botón de envío si existe
        if (this.submitButton) {
            this.submitButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.performSearch(true); // Forzar búsqueda completa
            });
        }

        // Evento de teclas (Enter para buscar, flechas para navegar autocompletado)
        this.inputElement.addEventListener('keydown', this.handleKeyDown.bind(this));
    }

    handleInput(e) {
        const query = this.inputElement.value.trim();
        
        // Limpiar el temporizador existente
        if (this.timer) {
            clearTimeout(this.timer);
        }

        // Si no hay texto o no alcanza el mínimo de caracteres, ocultar resultados
        if (query.length < this.options.minChars) {
            this.hideAutocomplete();
            return;
        }

        // Establecer un nuevo temporizador para la búsqueda
        this.timer = setTimeout(() => {
            // Primero mostrar autocompletado
            this.fetchAutocomplete(query);
            
            // Luego realizar la búsqueda si hay un contenedor de resultados
            if (this.resultsElement) {
                this.performSearch();
            }
        }, this.options.delay);
    }

    handleFocus(e) {
        const query = this.inputElement.value.trim();
        if (query.length >= this.options.minChars) {
            this.fetchAutocomplete(query);
        }
    }

    handleDocumentClick(e) {
        // Cerrar autocompletado si se hace clic fuera del input y del contenedor
        if (e.target !== this.inputElement && e.target !== this.autocompleteContainer) {
            this.hideAutocomplete();
        }
    }

    handleKeyDown(e) {
        // Enter para buscar
        if (e.key === 'Enter') {
            e.preventDefault();
            this.hideAutocomplete();
            this.performSearch(true); // Forzar búsqueda completa
        }
        
        // Flechas para navegar autocompletado
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            
            if (this.autocompleteContainer.style.display === 'none') {
                return;
            }
            
            const items = this.autocompleteContainer.querySelectorAll('.autocomplete-item');
            if (!items.length) return;
            
            const activeItem = this.autocompleteContainer.querySelector('.autocomplete-item.active');
            
            if (!activeItem) {
                // Si no hay elemento activo, activar el primero (abajo) o el último (arriba)
                const index = e.key === 'ArrowDown' ? 0 : items.length - 1;
                items[index].classList.add('active');
                this.inputElement.value = items[index].textContent;
            } else {
                // Si ya hay elemento activo, mover a siguiente/anterior
                activeItem.classList.remove('active');
                
                let newIndex;
                const currentIndex = Array.from(items).indexOf(activeItem);
                
                if (e.key === 'ArrowDown') {
                    newIndex = (currentIndex + 1) % items.length;
                } else {
                    newIndex = currentIndex - 1 < 0 ? items.length - 1 : currentIndex - 1;
                }
                
                items[newIndex].classList.add('active');
                this.inputElement.value = items[newIndex].textContent;
            }
        }
    }

    fetchAutocomplete(query) {
        const formData = new URLSearchParams();
        formData.append('q', query);
        formData.append('tipo', this.options.tipo);

        fetch(this.options.autocompleteUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                this.showAutocomplete(data);
            } else {
                this.hideAutocomplete();
            }
        })
        .catch(error => {
            console.error('Error en autocompletado:', error);
            this.hideAutocomplete();
        });
    }

    showAutocomplete(items) {
        // Limpiar contenedor
        this.autocompleteContainer.innerHTML = '';
        
        // Crear elementos para cada sugerencia
        items.forEach(item => {
            const element = document.createElement('div');
            element.className = 'autocomplete-item';
            element.textContent = item.text;
            if (item.id) {
                element.dataset.id = item.id;
            }
            
            // Evento al hacer clic en una sugerencia
            element.addEventListener('click', () => {
                this.inputElement.value = item.text;
                this.hideAutocomplete();
                this.performSearch(true); // Forzar búsqueda completa
            });
            
            this.autocompleteContainer.appendChild(element);
        });
        
        // Ajustar posición del contenedor
        const inputRect = this.inputElement.getBoundingClientRect();
        this.autocompleteContainer.style.width = inputRect.width + 'px';
        this.autocompleteContainer.style.top = (inputRect.bottom) + 'px';
        this.autocompleteContainer.style.left = inputRect.left + 'px';
        
        // Mostrar contenedor
        this.autocompleteContainer.style.display = 'block';
    }

    hideAutocomplete() {
        if (this.autocompleteContainer) {
            this.autocompleteContainer.style.display = 'none';
        }
    }

    performSearch(forceSearch = false) {
        const query = this.inputElement.value.trim();
        
        // No buscar si no hay texto suficiente y no es una búsqueda forzada
        if (query.length < this.options.minChars && !forceSearch) {
            return;
        }

        // Preparar datos para enviar
        const formData = new URLSearchParams();
        formData.append('q', query);
        formData.append('tipo', this.options.tipo);
        
        // Añadir filtros adicionales si están definidos en las opciones
        if (this.options.filters) {
            for (const [key, selector] of Object.entries(this.options.filters)) {
                const element = document.querySelector(selector);
                if (element) {
                    formData.append(key, element.value);
                }
            }
        }

        // Realizar petición AJAX
        fetch(this.options.searchUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            if (this.resultsElement) {
                this.resultsElement.innerHTML = html;
                
                // Ejecutar callback si está definido
                if (typeof this.options.onResultsLoaded === 'function') {
                    this.options.onResultsLoaded(html);
                }
            }
        })
        .catch(error => {
            console.error('Error en la búsqueda:', error);
            if (this.resultsElement) {
                this.resultsElement.innerHTML = '<div class="error-message">Ha ocurrido un error en la búsqueda</div>';
            }
        });
    }
}

// Estilos CSS para el autocompletado (se añaden dinámicamente)
document.addEventListener('DOMContentLoaded', function() {
    // Solo añadir los estilos si no existen ya
    if (!document.getElementById('buscador-styles')) {
        const styles = document.createElement('style');
        styles.id = 'buscador-styles';
        styles.textContent = `
            .autocomplete-container {
                position: absolute;
                z-index: 1000;
                background: white;
                border: 1px solid #ddd;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                max-height: 300px;
                overflow-y: auto;
            }
            .autocomplete-item {
                padding: 8px 12px;
                cursor: pointer;
                border-bottom: 1px solid #f0f0f0;
            }
            .autocomplete-item:last-child {
                border-bottom: none;
            }
            .autocomplete-item:hover, .autocomplete-item.active {
                background-color: #f0f0f0;
            }
        `;
        document.head.appendChild(styles);
    }
});

// Ejemplo de uso:
// const miBuscador = new Buscador({
//     inputSelector: '#mi-input-busqueda',
//     resultsSelector: '#mis-resultados',
//     tipo: 'servicios',
//     filters: {
//         'localidad': '#filtro_localidad',
//         'precio': '#filtro_precio'
//     }
// });
