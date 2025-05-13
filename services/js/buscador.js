/**
 * Buscador reutilizable con AJAX
 * Este script proporciona funcionalidad de búsqueda para cualquier página del sitio.
 */

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Buscar todos los elementos con la clase 'search-input' para inicializar
    const inputsBuscador = document.querySelectorAll('.search-input');
      // Inicializar cada buscador encontrado
    inputsBuscador.forEach(input => {
        // Encontrar el botón de búsqueda (si existe)
        const botonBuscar = input.parentElement.querySelector('.search-icon') || null;
        
        // Configurar evento clic del botón de búsqueda
        if (botonBuscar) {
            botonBuscar.addEventListener('click', function() {
                const termino = input.value.trim();
                
                // Solo realizar búsqueda si hay texto
                if (termino.length > 0) {
                    // Determinar la ruta correcta según la página actual
                    const isSubDir = window.location.pathname.includes('/services/') || 
                                   window.location.pathname.includes('/vistas_usuarios/') ||
                                   window.location.pathname.includes('/reservas/') ||
                                   window.location.pathname.includes('/portfolio/');
                    
                    const basePath = isSubDir ? '../' : '';
                    // Redirigir a la página de búsqueda con la consulta como parámetro q
                    window.location.href = `${basePath}services/buscarservicio.php?q=${encodeURIComponent(termino)}`;
                }
            });
        }
        
        // Configurar evento al presionar Enter
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const termino = this.value.trim();
                
                // Solo realizar búsqueda si hay texto
                if (termino.length > 0) {
                    // Determinar la ruta correcta según la página actual
                    const isSubDir = window.location.pathname.includes('/services/') || 
                                   window.location.pathname.includes('/vistas_usuarios/') ||
                                   window.location.pathname.includes('/reservas/') ||
                                   window.location.pathname.includes('/portfolio/');
                    
                    const basePath = isSubDir ? '../' : '';
                    // Redirigir a la página de búsqueda
                    window.location.href = `${basePath}services/buscarservicio.php?q=${encodeURIComponent(termino)}`;
                }
            }
        });// Configurar evento clic del botón de búsqueda
        if (botonBuscar) {
            botonBuscar.addEventListener('click', function() {
                const termino = input.value.trim();
                
                // Solo realizar búsqueda si hay texto
                if (termino.length > 0) {
                    // Determinar la ruta correcta según la página actual
                    const isSubDir = window.location.pathname.includes('/services/') || 
                                   window.location.pathname.includes('/vistas_usuarios/') ||
                                   window.location.pathname.includes('/reservas/') ||
                                   window.location.pathname.includes('/portfolio/');
                    
                    const basePath = isSubDir ? '../' : '';
                    // Redirigir a la página de búsqueda con la consulta como parámetro q
                    // La misma consulta puede ser nombre de servicio o localidad
                    window.location.href = `${basePath}services/buscarservicio.php?q=${encodeURIComponent(termino)}`;
                }
            });
        }        // Configurar evento al presionar Enter
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const termino = this.value.trim();
                
                // Solo realizar búsqueda si hay texto
                if (termino.length > 0) {
                    // Determinar la ruta correcta según la página actual
                    const isSubDir = window.location.pathname.includes('/services/') || 
                                   window.location.pathname.includes('/vistas_usuarios/') ||
                                   window.location.pathname.includes('/reservas/') ||
                                   window.location.pathname.includes('/portfolio/');
                    
                    const basePath = isSubDir ? '../' : '';
                    // Al igual que el botón, usamos solo el parámetro q
                    window.location.href = `${basePath}services/buscarservicio.php?q=${encodeURIComponent(termino)}`;
                }
            }
        });
      });
});
