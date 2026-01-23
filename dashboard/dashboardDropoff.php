<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'dropoff']);

$pdo = getPDO();


$stmt = $pdo->query("
    SELECT r.*, u.nombre, u.apellidos, u.dni,
           v.marca, v.modelo, c.nombreCategoria
    FROM Reserva r
    JOIN Usuario u ON r.idUsuario = u.idUsuario
    LEFT JOIN Vehiculo v ON r.idVehiculo = v.idVehiculo
    LEFT JOIN Categoria c ON r.idCategoria = c.idCategoria
    WHERE r.estado = 'CUBIERTA'
    ORDER BY r.fechaInicio DESC
");
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contador resumen
$totalCubiertas = count($reservas);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Dropoff - SilverGear Mobility</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php imprimirFavicon(); ?>
</head>

<body class="bg-light">

    <!-- Navbar -->
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

        <h1 class="mb-4">Dashboard Dropoff</h1>

        <!-- Resumen General -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Resumen General</h5>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Reservas cubiertas</h6>
                                <span class="fs-3 fw-bold"><?= $totalCubiertas ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gestión de Reservas -->
        <div class="card mb-4" id="reservas">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Gestión de Reservas</h5>
                    <div>
                        <button class="btn btn-primary" onclick="location.href='cerrarContrato.php'">
                            Cerrar Contrato
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>DNI Cliente</th>
                                <th>Cliente</th>
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
                                    <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']) ?></td>
                                    <td><?= htmlspecialchars($row['marca'] . ' ' . $row['modelo']) ?></td>
                                    <td><?= htmlspecialchars($row['nombreCategoria'] ?? 'Sin categoría') ?></td>
                                    <td><?= $row['fechaInicio'] ?></td>
                                    <td><?= $row['fechaFin'] ?></td>
                                    <td><?= $row['estado'] ?></td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($reservas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        No hay reservas cubiertas
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
