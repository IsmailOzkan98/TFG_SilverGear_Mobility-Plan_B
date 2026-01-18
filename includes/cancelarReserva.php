<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico']);

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idReserva = $_POST['idReserva'] ?? null;
    $idUsuario = $_SESSION['usuario']['idUsuario'];

    if (!$idReserva) {
        die("Reserva no válida.");
    }

    $stmt = $pdo->prepare("SELECT idUsuario FROM Reserva WHERE idReserva = :idReserva");
    $stmt->execute([':idReserva' => $idReserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        die("Reserva no encontrada.");
    }

    if ($reserva['idUsuario'] != $idUsuario && !in_array($_SESSION['usuario']['rol'], ['admin', 'ventas', 'mecanico'])) {
        die("No tienes permiso para cancelar esta reserva.");
    }

    $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'CANCELADA' WHERE idReserva = :idReserva");
    $stmt->execute([':idReserva' => $idReserva]);

    header("Location: ../pages/miPerfil.php");
    exit;
} else {
    die("Acceso no permitido.");
}
