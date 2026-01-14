<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
require_once '../includes/Vehiculo.php';

requireRole(['mecanico', 'admin']);

if (!isset($_GET['idVehiculo'])) {
    header('Location: dashboardAdmin.php');
    exit;
}

$pdo = getPDO();

// Cargar datos del vehículo
$idVehiculo = $_GET['idVehiculo'];
$stmt = $pdo->prepare("SELECT * FROM Vehiculo WHERE idVehiculo = :idVehiculo");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$datosDB = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datosDB) {
    header('Location: dashboardAdmin.php#vehiculos');
    exit;
}

$vehiculo = new Vehiculo($datosDB, $pdo);

// Obtener idEstado de BAJA
$stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado='BAJA'");
$stmtEstado->execute();
$idBaja = $stmtEstado->fetchColumn();

if ($idBaja) {
    // Cambiar estado y registrar historial globalmente
    $dniTrabajador = $_SESSION['usuario']['dni'] ?? null;
    cambiarEstadoVehiculo($pdo, $vehiculo, $idBaja, $dniTrabajador, 'Vehículo dado de baja');
}

header('Location: dashboardAdmin.php#vehiculos');
exit;
