<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['mecanico', 'admin']);

$pdo = getPDO();

// Vh
$stmt = $pdo->query("SELECT idEstado, nombreEstado FROM EstadoVehiculo");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$vehiculos = obtenerFlotaFiltrada($_GET);

// Filtros
$estados = $pdo->query("SELECT * FROM EstadoVehiculo")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT * FROM Categoria")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Mecánico - SilverGear Mobility</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<script src="../js/vehiculos.js"></script>

<body class="bg-light">


    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Mecanico</span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#flota">Consultar Flota</a></li>
                    <li class="nav-item"><a class="nav-link" href="../vehiculos/darAltaVehiculo.php">Dar de Alta Vehículo</a></li>
                    <li class="nav-item"><a class="nav-link" href="#historial">Historial Vehículos</a></li>
                    <?php if (getUserRole() === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold text-warning" href="<?= volverSegunRol() ?>">Volver a Dashboard Admin</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <h1 class="mb-4">Dashboard Mecanico</h1>


        <!-- Vehiculos -->
        <div class="card mb-4" id="vehiculos">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Gestión de Vehículos</h5>
                    <button class="btn btn-primary" onclick="location.href='darAltaVehiculo.php'">
                        Añadir Vehículo
                    </button>
                </div>

                <div class="card mb-4" id="flota">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Consultar Flota</h5>

                        <form method="GET" class="row g-2 align-items-end mb-3">

                            <div class="col">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($estados as $e): ?>
                                        <option value="<?= $e['idEstado'] ?>">
                                            <?= $e['nombreEstado'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col">
                                <label class="form-label">Marca</label>
                                <input type="text" name="marca" class="form-control" placeholder="Marca">
                            </div>

                            <div class="col">
                                <label class="form-label">Modelo</label>
                                <input type="text" name="modelo" class="form-control" placeholder="Modelo">
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

                            <div class="col">
                                <label class="form-label">Km Máx.</label>
                                <input type="number" name="km" class="form-control" placeholder="Km">
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-primary ">
                                    Filtrar
                                </button>
                            </div>

                            <div class="col-auto">
                                <a href="<?= volverSegunRol() . '#vehiculos' ?>" class="btn btn-danger">
                                    Quitar filtro
                                </a>
                            </div>

                        </form>

                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Matrícula</th>
                                        <th>Marca / Modelo</th>
                                        <th>Estado</th>
                                        <th>Kms</th>
                                        <th>Categoria</th>
                                        <th>Disponibilidad</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehiculos as $row): ?>
                                        <tr>
                                            <td><?= $row['matricula'] ?></td>
                                            <td><?= $row['marca'] . ' ' . $row['modelo'] ?></td>
                                            <td>
                                                <span class="badge bg-warning text-dark">
                                                    <?= $row['nombreEstado'] ?>
                                                </span>
                                            </td>
                                            <td><?= $row['kmActual'] ?></td>
                                            <td><?= $row['nombreCategoria'] ?></td>
                                            <td><?= $row['disponibilidad'] ? 'Disponible' : 'No disponible' ?></td>
                                            <td>
                                                <a href="editarVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary">Editar</a>
                                                <a href="venderVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary">Vender</a>
                                                <a href="historialVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary">Ver Historial</a>
                                                <button
                                                    class="btn btn-sm btn-danger"
                                                    onclick="confirmarBaja('<?= $row['idVehiculo'] ?>')">
                                                    Dar de baja
                                                </button>


                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>



    </div>

</body>

</html>