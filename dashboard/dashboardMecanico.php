<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['mecanico', 'admin']);

$pdo = getPDO();

// Vh
$stmt = $pdo->query("SELECT v.*, e.nombreEstado, c.nombreCategoria FROM Vehiculo v 
                     JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
                     JOIN Categoria c ON v.idCategoria = c.idCategoria");
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

<body class="bg-light">


    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Mecánico</span>
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
        <h1 class="mb-4">Panel Mecánico</h1>


        <div class="card mb-4" id="flota">
            <div class="card-body">
                <h5 class="card-title mb-3">Consultar Flota</h5>


                <form method="GET" class="row g-3 mb-3">
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e['idEstado'] ?>"><?= $e['nombreEstado'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Marca</label>
                        <input type="text" name="marca" class="form-control" placeholder="Marca">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Modelo</label>
                        <input type="text" name="modelo" class="form-control" placeholder="Modelo">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['idCategoria'] ?>"><?= $c['nombreCategoria'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Km Máx.</label>
                        <input type="number" name="km" class="form-control" placeholder="Km">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Filtrar</button>
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
                                <th>Categoría</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiculos as $v): ?>
                                <tr>
                                    <td><?= $v['matricula'] ?></td>
                                    <td><?= $v['marca'] . ' ' . $v['modelo'] ?></td>
                                    <td><span class="badge bg-warning text-dark"><?= $v['nombreEstado'] ?></span></td>
                                    <td><?= $v['kmActual'] ?></td>
                                    <td><?= $v['nombreCategoria'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="location.href='cambiarEstadoVehiculo.php?id=<?= $v['idVehiculo'] ?>'">Cambiar Estado</button>
                                        <button class="btn btn-sm btn-info" onclick="location.href='historialVehiculo.php?id=<?= $v['idVehiculo'] ?>'">Historial</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>



    </div>

</body>

</html>