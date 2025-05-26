<?php

/**
 * Valida un nombre o apellido
 * - Sin espacios al inicio/fin
 * - Sin espacios múltiples
 * - Solo letras y espacios simples
 */
function validarNombreApellido($texto) {
    // Eliminar espacios al inicio y final
    $texto = trim($texto);
    
    // Reemplazar múltiples espacios por uno solo
    $texto = preg_replace('/\s+/', ' ', $texto);
    
    // Verificar que solo contiene letras y espacios
    if (!preg_match('/^[A-Za-zÁáÉéÍíÓóÚúÑñ\s]+$/', $texto)) {
        return false;
    }
    
    return $texto;
}

/**
 * Valida un email según estándares comunes
 */
function validarEmail($email) {
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return $email;
}

/**
 * Valida un DNI/NIF español
 */
function validarDNINIF($dni) {
    $dni = strtoupper(trim($dni));
    
    // NIFs de personas físicas: 8 números + 1 letra
    if (preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
        $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
        $numero = substr($dni, 0, 8);
        $letra = $dni[8];
        if ($letra == $letras[((int)$numero) % 23]) {
            return $dni;
        }
    }
    
    // NIFs de personas jurídicas
    if (preg_match('/^[ABCDEFGHJKLMNPQRSUVW][0-9]{7}[0-9A-J]$/', $dni)) {
        return $dni;
    }
    
    // NIEs
    if (preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $dni)) {
        return $dni;
    }
    
    return false;
}

/**
 * Valida un número de teléfono español
 */
function validarTelefono($telefono) {
    $telefono = trim($telefono);
    
    // Eliminar espacios y guiones
    $telefono = str_replace([' ', '-'], '', $telefono);
    
    // Verificar formato español: fijo o móvil
    if (!preg_match('/^([679][0-9]{8})$/', $telefono)) {
        return false;
    }
    
    return $telefono;
}

/**
 * Limpia una dirección de caracteres especiales
 */
function validarDireccion($direccion) {
    $direccion = trim($direccion);
    
    // Eliminar múltiples espacios
    $direccion = preg_replace('/\s+/', ' ', $direccion);
    
    // Permitir letras, números, espacios y algunos caracteres especiales comunes en direcciones
    if (!preg_match('/^[A-Za-z0-9ÁáÉéÍíÓóÚúÑñ\s\.,\-\/ºª]+$/', $direccion)) {
        return false;
    }
    
    return $direccion;
}

/**
 * Valida una contraseña
 * - Mínimo 8 caracteres
 * - Al menos una letra mayúscula
 * - Al menos una letra minúscula
 * - Al menos un número
 */
function validarPassword($password) {
    if (strlen($password) < 8) {
        return false;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    return true;
}
