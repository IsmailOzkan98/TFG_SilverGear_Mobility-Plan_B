<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico']);
require_once '../includes/controlFlota.php';

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idVehiculo      = $_POST['idVehiculo'];
    $idCategoria     = $_POST['idCategoria'];
    $marcaSolicitada = $_POST['marcaSolicitada'];
    $modeloSolicitado = $_POST['modeloSolicitado'];
    $fechaInicio     = $_POST['fechaInicio'];
    $fechaFin        = $_POST['fechaFin'];
    $precioDia       = $_POST['precioDia'];
    $precioTotal     = $_POST['precioTotal'];
    $seguro          = isset($_POST['seguro']) ? 1 : 0;
    $carnetJoven     = isset($_POST['carnetJoven']) ? 1 : 0;
    $idUsuario       = $_SESSION['usuario']['idUsuario'];


    //Validacion
    $validacionFechas = validarFechaReserva($fechaInicio, $fechaFin);

    if ($validacionFechas !== true) {
        $_SESSION['error_reserva'] = $validacionFechas;
        header("Location: ../pages/articuloAlquiler.php?idVehiculo=" . $idVehiculo);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO Reserva 
        (idUsuario, idCategoria, marcaSolicitada, modeloSolicitado, idVehiculo, fechaInicio, fechaFin, precioDia, precioTotal, estado, seguro, carnetJoven)
        VALUES 
        (:idUsuario, :idCategoria, :marca, :modelo, :idVehiculo, :fechaInicio, :fechaFin, :precioDia, :precioTotal, 'NO CUBIERTA', :seguro, :carnetJoven)
    ");



    $stmt->execute([
        ':idUsuario'    => $idUsuario,
        ':idCategoria'  => $idCategoria,
        ':marca'        => $marcaSolicitada,
        ':modelo'       => $modeloSolicitado,
        ':idVehiculo'   => $idVehiculo,
        ':fechaInicio'  => $fechaInicio,
        ':fechaFin'     => $fechaFin,
        ':precioDia'    => $precioDia,
        ':precioTotal'  => $precioTotal,
        ':seguro'       => $seguro,
        ':carnetJoven'  => $carnetJoven
    ]);

    $idReserva = $pdo->lastInsertId();
    asignarVehiculoAReserva($idReserva);

    header("Location: ../pages/miPerfil.php");
    exit;
}
