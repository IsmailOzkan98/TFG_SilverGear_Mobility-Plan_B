<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'limpieza']);

$pdo = getPDO();


//reservas activas
$stmt = $pdo->query("
    SELECT r.*, u.nombre, u.apellidos, u.dni,
           v.marca, v.modelo, c.nombreCategoria
    FROM Reserva r
    JOIN Usuario u ON r.idUsuario = u.idUsuario
    LEFT JOIN Vehiculo v ON r.idVehiculo = v.idVehiculo
    LEFT JOIN Categoria c ON r.idCategoria = c.idCategoria
    WHERE r.estado IN ('NO CUBIERTA','CUBIERTA')
    ORDER BY r.fechaInicio DESC
");
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Contadores
$totalActivas = count($reservas);
$reservasCubiertas = array_filter($reservas, fn($r) => $r['estado'] === 'CUBIERTA');
$reservasNoCubiertas = array_filter($reservas, fn($r) => $r['estado'] === 'NO CUBIERTA');


//vehiculos sucios
$stmt = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$_GET['estado'] = 2;
$vehiculos = obtenerFlotaFiltrada($_GET);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Limpieza - SilverGear Mobility</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <!-- Nav -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility</span>

            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto">
                    <?php if (getUserRole() === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold text-warning" href="<?= volverSegunRol() ?>">
                                Volver a Dashboard Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="../index.php">INDEX</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pages/tiendaAlquiler.php">ALQUILER</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pages/tiendaComprar.php">VENTAS</a></li>
                    <li class="nav-item"><a class="nav-link" href="../pages/miPerfil.php">MI PERFIL</a></li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../includes/logout.php">
                            Cerrar sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">

        <h1 class="mb-4">Dashboard Limpieza</h1>

        <!-- Resumen general -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Resumen General</h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Reservas activas</h6>
                                <span class="fs-3 fw-bold"><?= $totalActivas ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Reservas cubiertas</h6>
                                <span class="fs-3 fw-bold"><?= count($reservasCubiertas) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Reservas no cubiertas</h6>
                                <span class="fs-3 fw-bold"><?= count($reservasNoCubiertas) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservas -->
        <div class="card mb-4" id="reservas">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Gestión de Reservas</h5>
                    <div>
                        <button class="btn btn-primary" onclick="location.href='cubrirReservas.php'">
                            Cubrir Reservas
                        </button>
                        <button class="btn btn-primary" onclick="location.href='entregarVehiculo.php'">
                            Entregar Vehículo
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>DNI Cliente</th>
                                <th>Vehículo</th>
                                <th>Categoría</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $row): ?>
                                <tr>
                                    <td><?= $row['idReserva'] ?></td>
                                    <td><?= htmlspecialchars($row['dni']) ?></td>
                                    <td><?= htmlspecialchars($row['marca'] . ' ' . $row['modelo']) ?></td>
                                    <td><?= htmlspecialchars($row['nombreCategoria'] ?? 'Sin categoría') ?></td>
                                    <td><?= $row['fechaInicio'] ?></td>
                                    <td><?= $row['fechaFin'] ?></td>
                                    <td><?= $row['estado'] ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        No hay reservas activas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Vehiculos -->
        <div class="card mb-4" id="vehiculos">
            <div class="card-body">
                <h5 class="mb-3">Vehículos Sucios</h5>

                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca" class="form-control">
                    </div>

                    <div class="col">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control">
                    </div>

                    <div class="col">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['idCategoria'] ?>">
                                    <?= $c['nombreCategoria'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-auto">
                        <button class="btn btn-primary">Filtrar</button>
                    </div>

                    <div class="col-auto">
                        <a href="<?= $_SERVER['PHP_SELF'] ?>#vehiculos" class="btn btn-danger">
                            Quitar filtros
                        </a>
                    </div>

                </form>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Matrícula</th>
                                <th>Marca / Modelo</th>
                                <th>Kms</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiculos as $row): ?>
                                <tr>
                                    <td><?= $row['matricula'] ?></td>
                                    <td><?= $row['marca'] . ' ' . $row['modelo'] ?></td>
                                    <td><?= $row['kmActual'] ?></td>
                                    <td><?= $row['nombreCategoria'] ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($vehiculos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        No hay vehículos sucios
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>

</html>