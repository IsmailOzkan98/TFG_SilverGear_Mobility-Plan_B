<?php
require_once '../includes/common.php';
require_once '../includes/security.php';

$pdo = getPDO();

function eliminarUsuario(int $idUsuario, PDO $pdo): string {
    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Guardar datos del usuario en histórico
        $stmt = $pdo->prepare("
            INSERT INTO Usuario_Eliminado (idUsuarioOriginal, dni, nombre, apellidos)
            SELECT idUsuario, dni, nombre, apellidos FROM Usuario WHERE idUsuario = ?
        ");
        $stmt->execute([$idUsuario]);
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            return "No se encontró usuario para archivar.";
        }

        // Obtener o crear usuario fantasma
        $stmt = $pdo->query("SELECT idUsuario FROM Usuario WHERE dni='00000000X'");
        $idFantasma = $stmt->fetchColumn();

        if (!$idFantasma) {
            $stmt = $pdo->prepare("
                INSERT INTO Usuario 
                (nombre, apellidos, dni, direccion, ciudad, pais, telefono, email, fechaNacimiento, fechaCarnet, contrasena, idRol)
                VALUES ('Usuario', 'Eliminado', '00000000X', 'N/A', 'N/A', 'N/A', '000000000', 'eliminado@system.local', '1900-01-01', '1900-01-01', 'N/A', 2)
            ");
            $stmt->execute();
            $idFantasma = $pdo->lastInsertId();
            if (!$idFantasma) {
                $pdo->rollBack();
                return "Error al crear usuario fantasma.";
            }
        }

        // Reasignar reservas
        $stmt = $pdo->prepare("UPDATE Reserva SET idUsuario = ? WHERE idUsuario = ?");
        $stmt->execute([$idFantasma, $idUsuario]);
        $reservas = $stmt->rowCount();

        // Limpiar incidencias
        $stmt = $pdo->prepare("UPDATE Incidencia SET idUsuario = NULL WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        $incidencias = $stmt->rowCount();

        // Eliminar usuario
        $stmt = $pdo->prepare("DELETE FROM Usuario WHERE idUsuario = ?");
        $stmt->execute([$idUsuario]);
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            return "No se pudo eliminar el usuario (posible restricción de FK).";
        }

        // Confirmar transacción
        $pdo->commit();

        return "Usuario eliminado correctamente. Reservas reasignadas: $reservas, incidencias desvinculadas: $incidencias.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        return "Error eliminando usuario: " . $e->getMessage();
    }
}


// ADMIN
if ($_SESSION['usuario']['rol'] === 'admin' && isset($_GET['idUsuario'])) {
    $idUsuario = intval($_GET['idUsuario']);

    // No permitir eliminar otros admins
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

// CLIENTE
if ($_SESSION['usuario']['rol'] === 'cliente') {
    $idUsuario = $_SESSION['usuario']['idUsuario'];
    $mensaje = eliminarUsuario($idUsuario, $pdo);

    session_destroy();
    header('Location: ../index.php?msg=' . urlencode($mensaje));
    exit;
}

// REDIRECCIÓN POR DEFECTO
header('Location: ../index.php');
exit;
