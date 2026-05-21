<?php
require_once 'common.php';
require_once 'Vehiculo.php';

/**
 * Obtiene un vehiculo compatible con los criterios
 * Prioridad: marca+modelo o cualquier vehiculo de la misma categoria
 * @param PDO $pdo
 * @param string|null $marca
 * @param string|null $modelo
 * @param string|int|null $idCategoria
 * @return Vehiculo|null
 */
function getVehiculoCompatible(PDO $pdo, ?string $marca, ?string $modelo, $idCategoria = null): ?Vehiculo
{
    $sql = "
        SELECT *
        FROM Vehiculo
        WHERE idEstado = 1 -- LIMPIO
          AND disponibilidad = 1
    ";
    $params = [];

    if ($marca) {
        $sql .= " AND marca = :marca";
        $params[':marca'] = $marca;
    }
    if ($modelo) {
        $sql .= " AND modelo = :modelo";
        $params[':modelo'] = $modelo;
    }

    if ($marca || $modelo) {
        $sql .= " ORDER BY idVehiculo ASC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $vehiculoDatos = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($vehiculoDatos) return new Vehiculo($vehiculoDatos, $pdo);
    }

    if ($idCategoria) {
        $sqlCat = "
            SELECT *
            FROM Vehiculo
            WHERE idEstado = 1
              AND disponibilidad = 1
              AND idCategoria = :idCategoria
            ORDER BY idVehiculo ASC LIMIT 1
        ";
        $stmt = $pdo->prepare($sqlCat);
        $stmt->execute([':idCategoria' => $idCategoria]);
        $vehiculoDatos = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($vehiculoDatos) return new Vehiculo($vehiculoDatos, $pdo);
    }

    return null;
}

/**
 * Asigna un vehiculo limpio a la reserva NO CUBIERTA
 * @param int $idReserva
 * @return bool
 */
function asignarVehiculoAReserva(int $idReserva): bool
{
    $pdo = getPDO();

    $stmt = $pdo->prepare("SELECT * FROM Reserva WHERE idReserva = :idReserva");
    $stmt->execute([':idReserva' => $idReserva]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva || $reserva['estado'] !== 'NO CUBIERTA') return false;

    $vehiculo = getVehiculoCompatible(
        $pdo,
        $reserva['marcaSolicitada'] ?? null,
        $reserva['modeloSolicitado'] ?? null,
        $reserva['idCategoria'] ?? null
    );

    if (!$vehiculo) return false;

    // Guardar matrícula en matriculaVehiculo, dejar idVehiculo intacto
    $stmtUpdate = $pdo->prepare("
        UPDATE Reserva
        SET matriculaVehiculo = :matricula, estado = 'CUBIERTA'
        WHERE idReserva = :idReserva
    ");
    $stmtUpdate->execute([
        ':matricula' => $vehiculo->matricula,
        ':idReserva' => $idReserva
    ]);

    // Cambiar estado del vehiculo a ALQUILADO
    $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = 'ALQUILADO'");
    $stmtEstado->execute();
    $idAlquilado = $stmtEstado->fetchColumn();

    cambiarEstadoVehiculo($pdo, $vehiculo, $idAlquilado, $_SESSION['usuario']['dni'] ?? null, "Asignado a reserva $idReserva");

    return true;
}

/**
 * Vincula un vehiculo limpio entregado a la primera reserva NO CUBIERTA compatible
 * @param Vehiculo $vehiculo
 * @return bool
 */
function vincularVehiculoANoCubiertas(Vehiculo $vehiculo): bool
{
    $pdo = getPDO();

    if ($vehiculo->idEstado != 1 || !$vehiculo->disponibilidad) return false;

    $stmt = $pdo->prepare("
        SELECT *
        FROM Reserva
        WHERE estado = 'NO CUBIERTA'
          AND idCategoria = :idCategoria
          AND (marcaSolicitada = :marca OR modeloSolicitado = :modelo)
        ORDER BY idReserva ASC
        LIMIT 1
    ");
    $stmt->execute([
        ':idCategoria' => $vehiculo->idCategoria,
        ':marca' => $vehiculo->marca,
        ':modelo' => $vehiculo->modelo
    ]);

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        $stmt = $pdo->prepare("
            SELECT *
            FROM Reserva
            WHERE estado = 'NO CUBIERTA'
              AND idCategoria = :idCategoria
            ORDER BY idReserva ASC
            LIMIT 1
        ");
        $stmt->execute([':idCategoria' => $vehiculo->idCategoria]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$reserva) return false;

    $stmtUpdate = $pdo->prepare("
        UPDATE Reserva
        SET matriculaVehiculo = :matricula, estado = 'CUBIERTA'
        WHERE idReserva = :idReserva
    ");
    $stmtUpdate->execute([
        ':matricula' => $vehiculo->matricula,
        ':idReserva' => $reserva['idReserva']
    ]);

    $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = 'ALQUILADO'");
    $stmtEstado->execute();
    $idAlquilado = $stmtEstado->fetchColumn();

    cambiarEstadoVehiculo($pdo, $vehiculo, $idAlquilado, $_SESSION['usuario']['dni'] ?? null, "Asignado a reserva {$reserva['idReserva']}");

    return true;
}

/**
 * Ejecutar asignación de todas reservas NO CUBIERTAS a vehiculos disponibles
 */
function intentarCubrirTodasReservas()
{
    $pdo = getPDO();

    $stmt = $pdo->query("SELECT idReserva FROM Reserva WHERE estado = 'NO CUBIERTA' ORDER BY idReserva ASC");
    $reservas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($reservas as $idReserva) {
        asignarVehiculoAReserva($idReserva);
    }
}
