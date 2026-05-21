<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'dropoff']);
require_once '../includes/Vehiculo.php';
require_once '../includes/controlFlota.php';

$pdo = getPDO();

$cliente = null;
$reservas = [];
$mensaje = '';
$errores = [];

//buscar por DNI
if (isset($_GET['dni'])) {
    $dni = strtoupper(trim($_GET['dni']));

    $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE dni = :dni");
    $stmt->execute([':dni' => $dni]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $errores['general'] = "Cliente no encontrado.";
    } else {
        //reservas CUBIERTA
        $stmtRes = $pdo->prepare("
            SELECT r.*, v.matricula, v.marca, v.modelo, v.idEstado
            FROM Reserva r
            LEFT JOIN Vehiculo v ON r.matriculaVehiculo = v.matricula
            WHERE r.idUsuario = :idUsuario AND r.estado = 'CUBIERTA'
            ORDER BY r.fechaInicio ASC
        ");
        $stmtRes->execute([':idUsuario' => $cliente['idUsuario']]);
        $reservas = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
    }
}

//crear contratro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrarContrato'])) {
    try {
        $idReserva = $_POST['idReserva'] ?? null;
        $penalizar = isset($_POST['penalizar']);
        $montoPenalizacion = $_POST['montoPenalizacion'] ?? 0;
        $notaPenalizacion = trim($_POST['notaPenalizacion'] ?? '');
        $estadoVehiculo = $_POST['estadoVehiculo'] ?? null;

        if (!isset($_POST['estadoVehiculo'])) {
            throw new Exception("Debes seleccionar el estado final del vehículo.");
        }

        $nombreEstado = $_POST['estadoVehiculo']; //SUCIO, IMPRO

        $stmtEstado = $pdo->prepare("
            SELECT idEstado 
            FROM EstadoVehiculo 
            WHERE nombreEstado = :nombre
        ");

        $stmtEstado->execute([':nombre' => $nombreEstado]);
        
        $idEstado = $stmtEstado->fetchColumn();

        if (!$idEstado) {
            throw new Exception("Estado del vehículo no válido.");
        }


        if (!$idReserva) throw new Exception("Reserva no válida.");
        if (!$estadoVehiculo || !in_array($estadoVehiculo, ['SUCIO', 'IMPRO'])) {
            throw new Exception("Estado del vehículo no válido.");
        }

        $stmtRes = $pdo->prepare("
            SELECT r.*, v.matricula, v.marca, v.modelo, v.idEstado
            FROM Reserva r
            LEFT JOIN Vehiculo v ON r.matriculaVehiculo = v.matricula
            WHERE r.idReserva = :idReserva AND r.estado = 'CUBIERTA'
        ");
        $stmtRes->execute([':idReserva' => $idReserva]);
        $datosReserva = $stmtRes->fetch(PDO::FETCH_ASSOC);

        if (!$datosReserva) throw new Exception("Reserva no encontrada o ya cerrada.");

        $vehiculo = new Vehiculo($datosReserva, $pdo);

        //Aplicar penalizacion
        if ($penalizar && $montoPenalizacion > 0) {
            $stmt = $pdo->prepare("
        INSERT INTO Penalizacion (idReserva, cantidad, nota, dniCliente, matriculaVehiculo)
        VALUES (:idReserva, :cantidad, :nota, :dniCliente, :matriculaVehiculo)
    ");
            $stmt->execute([
                ':idReserva'        => $idReserva,
                ':cantidad'         => $montoPenalizacion,
                ':nota'             => $notaPenalizacion,
                ':dniCliente'       => $cliente['dni'],
                ':matriculaVehiculo' => $vehiculo->matricula
            ]);
        }


        cambiarEstadoVehiculo(
            $pdo,
            $vehiculo,
            $idEstado,
            $_SESSION['usuario']['dni'] ?? null,
            'Cierre de contrato'
        );


        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'FINALIZADO' WHERE idReserva = :idReserva");
        $stmt->execute([':idReserva' => $idReserva]);

        $mensaje = "Contrato cerrado correctamente.";

        //Refrescar la lista
        if ($cliente) {
            $stmtRes = $pdo->prepare("
                SELECT r.*, v.matricula, v.marca, v.modelo, v.idEstado
                FROM Reserva r
                LEFT JOIN Vehiculo v ON r.matriculaVehiculo = v.matricula
                WHERE r.idUsuario = :idUsuario AND r.estado = 'CUBIERTA'
                ORDER BY r.fechaInicio ASC
            ");
            $stmtRes->execute([':idUsuario' => $cliente['idUsuario']]);
            $reservas = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php imprimirFavicon(); ?>
    <meta charset="UTF-8">
    <title>Cerrar Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function togglePenalizacion(id) {
            const check = document.getElementById('penalizar_' + id);
            const div = document.getElementById('penalizacionFields_' + id);
            div.style.display = check.checked ? 'block' : 'none';
        }
    </script>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Cerrar Contrato</span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="<?= volverSegunRol() ?>" class="nav-link">Volver</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Cerrar Contrato</h2>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($errores): ?>
            <div class="alert alert-danger"><?= htmlspecialchars(reset($errores)) ?></div>
        <?php endif; ?>

        <!-- Buscar cliente -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="dni" class="form-control" placeholder="DNI del cliente" required>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-primary">Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($cliente && !empty($reservas)): ?>
            <div class="card">
                <div class="card-body">
                    <h4>Contratos activos de <?= htmlspecialchars($cliente['nombre'] ?? '') ?></h4>
                    <?php foreach ($reservas as $reserva):
                        $veh = new Vehiculo($reserva, $pdo);
                        $id = $reserva['idReserva'];
                        $retrasoDias = comprobarRetrasoEntrega($reserva['fechaFin']);
                    ?>
                        <form method="post" class="border p-3 mb-3">
                            <input type="hidden" name="idReserva" value="<?= $id ?>">

                            <div class="row g-3 mb-2">
                                <div class="col-md-3">
                                    <label class="form-label">Matrícula</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($veh->matricula) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Marca</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($veh->marca) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Modelo</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($veh->modelo) ?>" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Estado actual</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars(Vehiculo::obtenerNombreEstado($veh->idEstado)) ?>" readonly>
                                </div>
                                <?php if ($retrasoDias !== null): ?>
                                    <div class="alert alert-warning mt-2">
                                        ⚠️ Vehículo entregado con <strong><?= $retrasoDias ?></strong> día(s) de retraso
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-check mb-2">
                                <input type="checkbox" class="form-check-input" name="penalizar" id="penalizar_<?= $id ?>" onclick="togglePenalizacion(<?= $id ?>)">
                                <label class="form-check-label" for="penalizar_<?= $id ?>">Penalizar</label>
                            </div>

                            <div id="penalizacionFields_<?= $id ?>" style="display:none;">
                                <div class="row g-3 mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Monto penalización (€)</label>
                                        <input type="number" step="0.01" name="montoPenalizacion" class="form-control">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Nota</label>
                                        <input type="text" name="notaPenalizacion" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label">Estado final del vehiculo</label>
                                    <select name="estadoVehiculo" class="form-select" required>
                                        <option value="">Seleccionar</option>
                                        <option value="SUCIO">SUCIO</option>
                                        <option value="IMPRO">IMPRO</option>
                                    </select>

                                </div>
                            </div>

                            <button type="submit" name="cerrarContrato" class="btn btn-primary mt-2">Cerrar Contrato</button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($cliente): ?>
            <div class="alert alert-info">No tiene contratos activos.</div>
        <?php endif; ?>

    </div>

</body>

</html>