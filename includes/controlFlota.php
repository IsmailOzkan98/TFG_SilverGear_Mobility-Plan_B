<?php
require_once 'common.php';
require_once 'Vehiculo.php';


//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------

//Al crear reserva mira flota disponible y mira si hay vehiculo compatible
function asignarVehiculoAReserva(int $idReserva): bool{
    $pdo = getPDO();

    $reserva = getReservaPorId($pdo, $idReserva);

    //validaciones
    if (!$reserva) return false; //existe

    if ($reserva['estado'] !== 'NO CUBIERTA') return false; //estado No Cubierta

    if (!fechaPermitida($reserva['fechaInicio'])) return false; //fecha en rango permitido

    $vehiculo = getVehiculoCompatible(
        $pdo,
        $reserva['marcaSolicitada'] ?? null,
        $reserva['modeloSolicitado'] ?? null,
        $reserva['idCategoria'] ?? null
    );

    if (!$vehiculo) return false;

    return asignarReserva($pdo, $vehiculo, $reserva);
}

//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------


// Asigna vehiculo entregado a una reserva no cubierta compatible
function vincularVehiculoANoCubiertas(Vehiculo $vehiculo): bool{
    $pdo = getPDO();

    //validaciones
    if ($vehiculo->idEstado != 1 || !$vehiculo->disponibilidad) {
        return false;
    }

    //Busca reservas y los filtra
    $reservas = filtrarReservasPorFecha(
        getReservasNoCubiertas($pdo)
    );

    $fallbackCategoria = null;

    //prioridad
    foreach ($reservas as $reserva) {

        // 1.
        //marca y modelo
        if (
            $reserva['marcaSolicitada'] === $vehiculo->marca &&
            $reserva['modeloSolicitado'] === $vehiculo->modelo
        ) {
            return asignarReserva($pdo, $vehiculo, $reserva);
        }

        //comprueba reserva por categoria
        if (
            !$fallbackCategoria &&
            $reserva['idCategoria'] == $vehiculo->idCategoria
        ) {
            $fallbackCategoria = $reserva;
        }
    }

    // 2.
    //categoria
    if ($fallbackCategoria) {
        return asignarReserva($pdo, $vehiculo, $fallbackCategoria);
    }

    return false;
}


//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------


// Busca todas reservas existentes e intenta cubrirlas con todos vehiculos Limpios
function intentarCubrirTodasReservas(){
    $pdo = getPDO();

    $reservas = getReservasNoCubiertas($pdo);

    $reservasValidas = filtrarReservasPorFecha($reservas);

    foreach ($reservasValidas as $reserva) {
        asignarVehiculoAReserva((int)$reserva['idReserva']);
    }
}

//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------
//-----------------------------------------------------------------------------------

//Logica reutilizable

//control por fecha
function fechaPermitida(string $fechaInicioReserva): bool
{
    $hoy = date('Y-m-d');
    $inicio = date('Y-m-d', strtotime($fechaInicioReserva));
    $diaAnterior = date('Y-m-d', strtotime($fechaInicioReserva . ' -1 day'));

    return ($hoy === $inicio || $hoy === $diaAnterior);
}

//obtener id de estado
function getEstadoId(PDO $pdo, string $nombre): int
{
    static $cache = [];

    if (isset($cache[$nombre])) {
        return $cache[$nombre];
    }

    $stmt = $pdo->prepare("
        SELECT idEstado
        FROM EstadoVehiculo
        WHERE nombreEstado = :nombre
    ");

    $stmt->execute([':nombre' => $nombre]);

    return $cache[$nombre] = (int)$stmt->fetchColumn();
}

//orden de prioridad - Marca y modelolo si no por categoria
function getVehiculoCompatible(PDO $pdo, ?string $marca, ?string $modelo, $idCategoria = null): ?Vehiculo{
    $sql = "
        SELECT *
        FROM Vehiculo
        WHERE idEstado = 1
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

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) return new Vehiculo($data, $pdo);
    }

    if ($idCategoria) {
        $stmt = $pdo->prepare("
            SELECT *
            FROM Vehiculo
            WHERE idEstado = 1
              AND disponibilidad = 1
              AND idCategoria = :idCategoria
            ORDER BY idVehiculo ASC
            LIMIT 1
        ");

        $stmt->execute([':idCategoria' => $idCategoria]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) return new Vehiculo($data, $pdo);
    }

    return null;
}


//obtener todas reservas no cubiertas
function getReservasNoCubiertas(PDO $pdo): array{
    $stmt = $pdo->query("
        SELECT *
        FROM Reserva
        WHERE estado = 'NO CUBIERTA'
        ORDER BY fechaInicio ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//Filtrar reserva por fecha
function filtrarReservasPorFecha(array $reservas): array{
    $validas = [];

    foreach ($reservas as $r) {
        if (fechaPermitida($r['fechaInicio'])) {
            $validas[] = $r;
        }
    }

    return $validas;
}

//Asignar vehiculo a una reserva
function asignarReserva(PDO $pdo, Vehiculo $vehiculo, array $reserva): bool{
    $stmt = $pdo->prepare("
        UPDATE Reserva
        SET matriculaVehiculo = :matricula,
            estado = 'CUBIERTA'
        WHERE idReserva = :idReserva
    ");

    $stmt->execute([
        ':matricula' => $vehiculo->matricula,
        ':idReserva' => $reserva['idReserva']
    ]);

    $idAlquilado = getEstadoId($pdo, 'ALQUILADO');

    cambiarEstadoVehiculo(
        $pdo,
        $vehiculo,
        $idAlquilado,
        $_SESSION['usuario']['dni'] ?? null,
        "Asignado a reserva {$reserva['idReserva']}"
    );

    return true;
}

//Buscar reserva por id
function getReservaPorId(PDO $pdo, int $idReserva): ?array{
    $stmt = $pdo->prepare("
        SELECT *
        FROM Reserva
        WHERE idReserva = :idReserva
        LIMIT 1
    ");

    $stmt->execute([':idReserva' => $idReserva]);

    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    return $reserva ?: null;
}