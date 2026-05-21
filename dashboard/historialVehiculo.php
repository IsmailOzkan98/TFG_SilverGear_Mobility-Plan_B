<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
require_once '../includes/Vehiculo.php';
requireRole(['admin', 'mecanico', 'ventas']);

$pdo = getPDO();

//Obtener ID
$idVehiculo = $_GET['idVehiculo'] ?? null;
if (!$idVehiculo) die("Vehículo no especificado.");

//Datos
$stmtVeh = $pdo->prepare("SELECT matricula, marca, modelo, idEstado FROM Vehiculo WHERE idVehiculo=:idVehiculo");
$stmtVeh->execute([':idVehiculo' => $idVehiculo]);
$vehiculo = $stmtVeh->fetch(PDO::FETCH_ASSOC);
if (!$vehiculo) die("Vehículo no encontrado.");

//Historial
$stmtHist = $pdo->prepare("
    SELECT vh.accion, vh.descripcion, vh.fechaHora, vh.dniTrabajador, ev.nombreEstado
    FROM Vehiculo_Historial vh
    LEFT JOIN EstadoVehiculo ev ON vh.idVehiculo = :idVehiculo AND ev.idEstado = vh.idVehiculo
    WHERE vh.idVehiculo=:idVehiculo
    ORDER BY vh.fechaHora DESC
");
$stmtHist->execute([':idVehiculo' => $idVehiculo]);
$historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial del Vehículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php imprimirFavicon(); ?>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Historial</span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="<?= volverSegunRol() ?>" class="nav-link">Volver</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="mb-4">Historial del Vehículo</h1>

                <div class="mb-3">
                    <strong>Matrícula:</strong> <?= htmlspecialchars($vehiculo['matricula']) ?><br>
                    <strong>Marca:</strong> <?= htmlspecialchars($vehiculo['marca']) ?><br>
                    <strong>Modelo:</strong> <?= htmlspecialchars($vehiculo['modelo']) ?><br>
                    <strong>Estado actual:</strong> <?= Vehiculo::obtenerNombreEstado($vehiculo['idEstado']) ?>
                </div>

                <?php if ($historial): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Acción</th>
                                <th>Descripción</th>
                                <th>DNI Trabajador</th>
                                <th>Fecha y hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $h): ?>
                                <tr>
                                    <td><?= htmlspecialchars($h['accion']) ?></td>
                                    <td><?= htmlspecialchars($h['descripcion']) ?></td>
                                    <td><?= htmlspecialchars($h['dniTrabajador'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($h['fechaHora']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No hay historial registrado para este vehículo.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>