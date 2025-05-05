document.addEventListener('DOMContentLoaded', function () {
    const inputBusqueda = document.getElementById('busqueda');
    const btnBuscar = document.getElementById('btn-buscar');
    const resultados = document.querySelector('.servicios-grid'); // donde se cargan los servicios
    const filtroLocalidad = document.getElementById('filtro_localidad');
    const filtroPrecio = document.getElementById('filtro_precio');
    const filtroDuracion = document.getElementById('filtro_duracion');
    const ordenAsc = document.getElementById('orden_asc');
    const ordenDesc = document.getElementById('orden_desc');

    let orden = '';

    function cargarServicios() {
        const busqueda = inputBusqueda.value;
        const localidad = filtroLocalidad.value;
        const precio = filtroPrecio.value;
        const duracion = filtroDuracion.value;

        const formData = new URLSearchParams();
        formData.append('q', busqueda);
        formData.append('localidad', localidad);
        formData.append('precio', precio);
        formData.append('duracion', duracion);
        formData.append('orden', orden);

        fetch('buscarservicio.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.text())
            .then(html => {
                resultados.innerHTML = html;
            })
            .catch(error => {
                console.error('Error al cargar los servicios:', error);
            });
    }

    inputBusqueda.addEventListener('input', cargarServicios);
    filtroLocalidad.addEventListener('change', cargarServicios);
    filtroPrecio.addEventListener('change', cargarServicios);
    filtroDuracion.addEventListener('change', cargarServicios);
    ordenAsc.addEventListener('click', () => {
        orden = 'asc';
        cargarServicios();
    });
    ordenDesc.addEventListener('click', () => {
        orden = 'desc';
        cargarServicios();
    });

    btnBuscar.addEventListener('click', function (e) {
        e.preventDefault();
        const query = encodeURIComponent(inputBusqueda.value.trim());
        if (query) {
            window.location.href = `buscarservicio.php?q=${query}`;
        }
    });

    // Cargar servicios iniciales al abrir la página
    cargarServicios();
});
