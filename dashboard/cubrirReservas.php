<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin','ventas']);
require_once '../includes/controlFlota.php';

try {
    intentarCubrirTodasReservas();
    $_SESSION['mensaje'] = "Se intento cubrir todas las reservas NO CUBIERTAS con flota disponible";
} catch (Exception $e) {
    $_SESSION['error'] = "Error al cubrir reservas: " . $e->getMessage();
}

header('Location: ' . volverSegunRol() . '#reservas');
exit;
