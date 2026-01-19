<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico', 'limpieza']);
require_once '../includes/Vehiculo.php';

$pdo = getPDO();

$vehiculo = null;
$datosVehiculo = null;
$mensaje = '';
$errores = [];


if (isset($_GET['matricula'])) {
    $matricula = strtoupper(trim($_GET['matricula']));

    $stmt = $pdo->prepare("
        SELECT v.*, c.nombreCategoria, e.nombreEstado
        FROM Vehiculo v
        JOIN Categoria c ON v.idCategoria = c.idCategoria
        JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
        WHERE v.matricula = :matricula
    ");
    $stmt->execute([':matricula' => $matricula]);
    $datosVehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datosVehiculo) {
        $errores['general'] = "Vehiculo no encontrado.";
    } else {
        $vehiculo = new Vehiculo($datosVehiculo, $pdo);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entregarVehiculo'])) {
    try {
        $matricula = $_POST['matricula'] ?? null;
        $plaza = trim($_POST['plazaParking'] ?? '');

        if (!$matricula) {
            throw new Exception("Vehiculo no valido.");
        }

        if ($plaza === '') {
            throw new Exception("La plaza de parking es obligatoria.");
        }

        //Cargar por matricula
        $stmt = $pdo->prepare("
            SELECT v.*, c.nombreCategoria, e.nombreEstado
            FROM Vehiculo v
            JOIN Categoria c ON v.idCategoria = c.idCategoria
            JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
            WHERE v.matricula = :matricula
        ");
        $stmt->execute([':matricula' => $matricula]);
        $datosVehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$datosVehiculo) {
            throw new Exception("Vehiculo no encontrado.");
        }

        $vehiculo = new Vehiculo($datosVehiculo, $pdo);

        //Estado SUCIO
        $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = 'SUCIO'");
        $stmtEstado->execute();
        $idSucio = $stmtEstado->fetchColumn();

        if ($vehiculo->idEstado != $idSucio) {
            throw new Exception("Solo se pueden entregar vehiculos en estado SUCIO.");
        }

        //Guardar plaza de parking
        $stmt = $pdo->prepare("
            UPDATE Vehiculo
            SET plazaParking = :plaza
            WHERE matricula = :matricula
        ");
        $stmt->execute([
            ':plaza' => $plaza,
            ':matricula' => $vehiculo->matricula
        ]);

        //Estado 
        $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = 'LIMPIO'");
        $stmtEstado->execute();
        $idLimpio = $stmtEstado->fetchColumn();

        $dniTrabajador = $_SESSION['usuario']['dni'] ?? null;
        cambiarEstadoVehiculo(
            $pdo,
            $vehiculo,
            $idLimpio,
            $dniTrabajador,
            "Vehiculo entregado en plaza $plaza"
        );

        $mensaje = "Vehiculo entregado correctamente y marcado como LIMPIO.";
        $vehiculo = null;
        $datosVehiculo = null;

    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entregar vehiculo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">SilverGear Mobility - Entregar Vehiculo</span>
        <div class="collapse navbar-collapse show">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="<?= volverSegunRol() ?>" class="nav-link">Volver</a>
                </li>
                <li class="nav-item">
                    <a href="../includes/logout.php" class="nav-link text-danger">Cerrar sesion</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($errores): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars(reset($errores)) ?>
        </div>
    <?php endif; ?>

    <!-- Buscar -->
    <div class="card mb-4">
        <div class="card-body">
            <h4>Buscar vehiculo por matricula</h4>
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="matricula" class="form-control" placeholder="Matricula" required>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($vehiculo): ?>
        <!-- Datos -->
        <div class="card">
            <div class="card-body">
                <h4 class="mb-3">Datos del vehiculo</h4>

                <form method="post">
                    <input type="hidden" name="matricula" value="<?= htmlspecialchars($vehiculo->matricula) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Matricula</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo->matricula) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Categoria</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($datosVehiculo['nombreCategoria']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo->marca) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo->modelo) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado actual</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($datosVehiculo['nombreEstado']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Plaza de parking</label>
                            <input type="text" name="plazaParking" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="entregarVehiculo" class="btn btn-primary mt-3">
                                Entregar vehiculo
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
