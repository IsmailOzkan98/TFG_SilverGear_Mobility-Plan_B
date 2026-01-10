<?php
require_once '../includes/common.php';
require_once '../includes/security.php';

requireRole(['mecanico', 'admin']);

if (!isset($_GET['idVehiculo'])) {
    header('Location: dashboardAdmin.php');
    exit;
}

$pdo = getPDO();

$stmt = $pdo->prepare("
    UPDATE Vehiculo 
    SET idEstado = (
        SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = 'BAJA'
    ),
    disponibilidad = 0
    WHERE idVehiculo = :id
");

$stmt->execute(['id' => $_GET['idVehiculo']]);

header('Location: dashboardAdmin.php#vehiculos');
exit;
