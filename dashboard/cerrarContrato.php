<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'ventas']);
require_once '../includes/Vehiculo.php';

$pdo = getPDO();

$cliente = null;
$reservas = [];
$vehiculo = null;
$datosReserva = null;
$mensaje = '';
$errores = [];

// Buscar cliente por DNI
if (isset($_GET['dni'])) {
    $dni = strtoupper(trim($_GET['dni']));

    $stmt = $pdo->prepare("SELECT * FROM Usuario WHERE dni = :dni");
    $stmt->execute([':dni' => $dni]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        $errores['general'] = "Cliente no encontrado.";
    } else {
        // Obtener reservas activas
        $stmtRes = $pdo->prepare("
            SELECT r.*, v.matricula, v.marca, v.modelo, v.idEstado
            FROM Reserva r
            JOIN Vehiculo v ON r.idVehiculo = v.idVehiculo
            WHERE r.idUsuario = :idUsuario AND r.estado = 'CUBIERTO'
        ");
        $stmtRes->execute([':idUsuario' => $cliente['idUsuario']]);
        $reservas = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($reservas)) {
            // Tomamos la primera reserva para cerrar
            $datosReserva = $reservas[0];
            $vehiculo = new Vehiculo($datosReserva, $pdo);
        }
    }
}

// Cerrar contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrarContrato'])) {
    try {
        $idReserva = $_POST['idReserva'] ?? null;
        $penalizar = isset($_POST['penalizar']);
        $montoPenalizacion = $_POST['montoPenalizacion'] ?? 0;
        $notaPenalizacion = trim($_POST['notaPenalizacion'] ?? '');
        $estadoVehiculo = $_POST['estadoVehiculo'] ?? null;

        if (!$idReserva) throw new Exception("Reserva no válida.");
        if (!$estadoVehiculo || !in_array($estadoVehiculo, ['SUCIO', 'IMPRO'])) {
            throw new Exception("Estado del vehículo no válido.");
        }

        // Cargar reserva
        $stmt = $pdo->prepare("
            SELECT r.*, v.matricula, v.marca, v.modelo, v.idEstado
            FROM Reserva r
            JOIN Vehiculo v ON r.idVehiculo = v.idVehiculo
            WHERE r.idReserva = :idReserva
        ");
        $stmt->execute([':idReserva' => $idReserva]);
        $datosReserva = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$datosReserva) throw new Exception("Reserva no encontrada.");

        $vehiculo = new Vehiculo($datosReserva, $pdo);

        // Aplicar penalización si marcada
        if ($penalizar && $montoPenalizacion > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO Penalizacion (idReserva, monto, nota)
                VALUES (:idReserva, :monto, :nota)
            ");
            $stmt->execute([
                ':idReserva' => $idReserva,
                ':monto' => $montoPenalizacion,
                ':nota' => $notaPenalizacion
            ]);
        }

        // Cambiar estado del vehículo
        $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = :nombre");
        $stmtEstado->execute([':nombre' => $estadoVehiculo]);
        $idEstado = $stmtEstado->fetchColumn();
        cambiarEstadoVehiculo($pdo, $vehiculo, $idEstado, $_SESSION['usuario']['dni'] ?? null, "Contrato cerrado");

        // Cambiar estado de la reserva a FINALIZADO
        $stmt = $pdo->prepare("UPDATE Reserva SET estado = 'FINALIZADO' WHERE idReserva = :idReserva");
        $stmt->execute([':idReserva' => $idReserva]);

        $mensaje = "Contrato cerrado correctamente.";
        $datosReserva = null;
        $vehiculo = null;
    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cerrar Contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function togglePenalizacion() {
            const check = document.getElementById('penalizar');
            const div = document.getElementById('penalizacionFields');
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

        <?php if ($datosReserva && $vehiculo): ?>
            <div class="card">
                <div class="card-body">
                    <h4>Datos del Vehículo</h4>
                    <form method="post">
                        <input type="hidden" name="idReserva" value="<?= $datosReserva['idReserva'] ?>">

                        <div class="row g-3 mb-2">
                            <div class="col-md-4">
                                <label class="form-label">Matrícula</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo->matricula) ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Marca</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo->marca) ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modelo</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo->modelo) ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado actual</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($datosReserva['idEstado']) ?>" readonly>
                            </div>
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="penalizar" id="penalizar" onclick="togglePenalizacion()">
                            <label class="form-check-label" for="penalizar">Penalizar</label>
                        </div>

                        <div id="penalizacionFields" style="display:none;">
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

                        <button type="submit" name="cerrarContrato" class="btn btn-primary mt-3">Cerrar Contrato</button>
                    </form>
                </div>
            </div>
        <?php elseif ($cliente): ?>
            <div class="alert alert-info">No tiene contratos activos.</div>
        <?php endif; ?>
    </div>

</body>

</html>