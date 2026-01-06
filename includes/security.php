<?php

require_once 'common.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtener el rol del usuario desde la sesión
 * @return string|null
 */
function getUserRole(): ?string {
    // Compatibilidad con distintos nombres de clave
    return $_SESSION['usuario']['rol'] ?? $_SESSION['usuario']['nombreRol'] ?? null;
}

/**
 * Comprobar si el usuario tiene un rol permitido
 * @param array $rolesPermitidos
 * @return bool
 */
function hasRole(array $rolesPermitidos): bool {
    $rol = getUserRole();
    if ($rol === null) return false;
    return in_array($rol, $rolesPermitidos);
}

/**
 * Verificar sesión y roles permitidos
 * @param array $rolesPermitidos
 */
function requireRole(array $rolesPermitidos) {
    // No hay usuario logueado
    if (!isset($_SESSION['usuario'])) {
        header('Location: ../welcome.php');
        exit;
    }

    // Usuario logueado pero rol no permitido
    if (!hasRole($rolesPermitidos)) {
        header('Location: ../welcome.php');
        exit;
    }
}
