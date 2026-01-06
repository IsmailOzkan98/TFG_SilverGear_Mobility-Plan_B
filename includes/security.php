<?php

require_once 'common.php'; 

// iniciar si no esta iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * comprobar que usuario tenga rol permitido
 * @param array $rolesPermitidos
 * @return bool
 */
function hasRole(array $rolesPermitidos): bool {
    if (!isset($_SESSION['usuario']['rol'])) return false;
    return in_array($_SESSION['usuario']['rol'], $rolesPermitidos);
}

/**
 * controla roles y sesiones
 * @param array $rolesPermitidos
 */
function requireRole(array $rolesPermitidos) {
    if (!isset($_SESSION['usuario'])) {
        header('Location: ../welcome.php');
        exit;
    }

    if (!hasRole($rolesPermitidos)) {
        header('Location: ../welcome.php');
        exit;
    }
}
