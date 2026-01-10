<?php
require_once '../includes/common.php';
require_once '../includes/security.php';

$pdo = getPDO();

function eliminarUsuario(int $idUsuario, PDO $pdo): string {

    //Guardar X datos de user en user historico
    $stmt = $pdo->prepare("
        INSERT INTO Usuario_Eliminado (idUsuarioOriginal, dni, nombre, apellidos)
        SELECT idUsuario, dni, nombre, apellidos FROM Usuario WHERE idUsuario = ?
    ");
    $stmt->execute([$idUsuario]);

    //obtener id de user fantasma
    $stmt = $pdo->query("SELECT idUsuario FROM Usuario WHERE dni='00000000X'");
    $idFantasma = $stmt->fetchColumn();

    //si no existe crearlo
    if (!$idFantasma) {
        $stmt = $pdo->prepare("
            INSERT INTO Usuario 
            (nombre, apellidos, dni, direccion, ciudad, pais, telefono, email, fechaNacimiento, fechaCarnet, contrasena, idRol)
            VALUES ('Usuario', 'Eliminado', '00000000X', 'N/A', 'N/A', 'N/A', '000000000', 'eliminado@system.local', '1900-01-01', '1900-01-01', 'N/A', 2)
        ");
        $stmt->execute();
        $idFantasma = $pdo->lastInsertId();
    }

    //dar todas reservas de usuario normal a fantasma
    $stmt = $pdo->prepare("UPDATE Reserva SET idUsuario = ? WHERE idUsuario = ?");
    $stmt->execute([$idFantasma, $idUsuario]);

    
    $stmt = $pdo->prepare("UPDATE Incidencia SET idUsuario = NULL WHERE idUsuario = ?");
    $stmt->execute([$idUsuario]);

    //Eliminar usuario
    $stmt = $pdo->prepare("DELETE FROM Usuario WHERE idUsuario = ?");
    $stmt->execute([$idUsuario]);

    return "Usuario eliminado.";
}


if (isset($_GET['idUsuario']) && ($_SESSION['usuario']['rol'] === 'admin')) {
    $idUsuario = intval($_GET['idUsuario']);

    //Admin no puede eliminar otros admins
    $stmt = $pdo->prepare("SELECT r.nombreRol FROM Usuario u JOIN Rol r ON u.idRol = r.idRol WHERE u.idUsuario = ?");
    $stmt->execute([$idUsuario]);
    $rol = $stmt->fetchColumn();

    if ($rol === 'admin') {
        header('Location: ../dashboard/dashboardAdmin.php#clientes?msg=' . urlencode('No se puede eliminar otro administrador'));
        exit;
    }

    $mensaje = eliminarUsuario($idUsuario, $pdo);
    header('Location: ../dashboard/dashboardAdmin.php#clientes?msg=' . urlencode($mensaje));
    exit;
}

//Cliente elimina su propia cuenta
if ($_SESSION['usuario']['rol'] === 'cliente') {
    $idUsuario = $_SESSION['usuario']['idUsuario'];
    $mensaje = eliminarUsuario($idUsuario, $pdo);

    // Cerrar sesion
    session_destroy();
    header('Location: ../index.php?msg=' . urlencode($mensaje));
    exit;
}

header('Location: ../index.php');
exit;
