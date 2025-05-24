<?php
// Determinar la ruta correcta para el ícono de búsqueda
$lupa_path = isset($base_path) ? $base_path . 'media/lupa.png' : 'media/lupa.png';
?>
<div class="search-box">
    <input type="text" 
           id="busqueda-principal" 
           placeholder="Buscar por servicio o ciudad..." 
           class="search-input">
    <img src="<?= $lupa_path ?>" alt="Buscar" class="search-icon">
</div>
