<?php
// Config/permissions.php - Control Centralizado de Roles y Permisos

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtener el rol estandarizado del usuario en sesión actual
 */
function get_user_role() {
    $rol = $_SESSION['rol'] ?? 'Socio';
    if (in_array($rol, ['Administrador', 'Infoser 76', 'Super Usuario'])) {
        return 'Super Usuario';
    }
    if ($rol === 'Tesorero') {
        return 'Contador';
    }
    return $rol;
}

/**
 * Verificar si el usuario es Super Usuario (Infoser 76)
 */
function is_super_user() {
    $rol = $_SESSION['rol'] ?? '';
    return in_array($rol, ['Super Usuario', 'Infoser 76', 'Administrador']);
}

/**
 * Verificar si el usuario es el Presidente
 */
function is_presidente() {
    return ($_SESSION['rol'] ?? '') === 'Presidente';
}

/**
 * Verificar si el usuario es Contador / Tesorero
 */
function is_contador() {
    $rol = $_SESSION['rol'] ?? '';
    return in_array($rol, ['Contador', 'Tesorero']);
}

/**
 * Verificar si el usuario es un Socio miembro
 */
function is_socio() {
    return ($_SESSION['rol'] ?? '') === 'Socio';
}

/**
 * Verificar si el usuario actual tiene acceso a un módulo o acción específica
 */
function check_permission($module) {
    $rol = get_user_role();

    // 1. Super Usuario (Infoser 76): Acceso completo a todo el sistema sin restricciones
    if (is_super_user()) {
        return true;
    }

    // 2. Presidente: Acceso a todo el sistema, salvo gestión de Usuarios del Sistema (restringido por Super Usuario)
    if (is_presidente()) {
        if ($module === 'usuarios') {
            return false; // Restricción impuesta por Super Usuario
        }
        return true;
    }

    // 3. Contador: Acceso completo a toda la parte contable y financiera del sistema
    if (is_contador()) {
        $allowed_modules = [
            'dashboard', 
            'socios', 
            'mensualidades', 
            'matriz_pagos', 
            'aporte_extraordinario', 
            'matriz_aportes', 
            'aporte_especial', 
            'cuota_inicial', 
            'categorias_ingreso', 
            'otros_ingresos', 
            'sueldo', 
            'servicios', 
            'egreso_extraordinario', 
            'egreso_especial', 
            'otros_gastos', 
            'activos', 
            'eventos', 
            'reportes'
        ];
        return in_array($module, $allowed_modules);
    }

    // 4. Asociados / Socio: Acceso restringido únicamente a ver eventos y consultar sus propias mensualidades y aportes
    if (is_socio()) {
        $allowed_modules = [
            'dashboard', 
            'eventos', 
            'mis_mensualidades', 
            'mis_aportes',
            'mis_aportes_especiales'
        ];
        return in_array($module, $allowed_modules);
    }

    return false;
}

/**
 * Proteger una página o controlador exigiendo permiso para el módulo
 */
function require_permission($module) {
    if (!check_permission($module)) {
        header("Location: ../View/dashboard.php?error=acceso_denegado");
        exit();
    }
}
?>
