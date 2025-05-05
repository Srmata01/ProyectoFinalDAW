$(document).ready(function () {
    // Autocompletado
    $('#busqueda').on('input', function () {
        let query = $(this).val();
        if (query.length > 2) {
            $.get('autocompletar.php', { q: query }, function (data) {
                $('#resultados').html(data);
            });
        }
    });

    // Filtrado y ordenado
    $('#filtro_precio, #filtro_duracion, #filtro_localidad').on('change', function () {
        buscarServicios();
    });

    $('#orden_asc').click(function () {
        $('#busqueda').data('orden', 'asc');
        buscarServicios();
    });

    $('#orden_desc').click(function () {
        $('#busqueda').data('orden', 'desc');
        buscarServicios();
    });

    function buscarServicios() {
        const busqueda = $('#busqueda').val();
        const localidad = $('#filtro_localidad').val();
        const precio = $('#filtro_precio').val();
        const duracion = $('#filtro_duracion').val();
        const orden = $('#busqueda').data('orden') || '';

        $.get('buscarservicio.php', {
            q: busqueda,
            localidad: localidad,
            precio: precio,
            duracion: duracion,
            orden: orden
        }, function (data) {
            $('#resultados').html(data);
        });
    }
});
