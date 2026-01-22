<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['mecanico', 'admin']);

$pdo = getPDO();
$testeoPagina = false;


//Total flota
$totalFlota = $pdo->query("SELECT COUNT(*) FROM Vehiculo")->fetchColumn();

//Alquilados
$alquilados = $pdo->query("SELECT COUNT(*) FROM Vehiculo WHERE idEstado = 7")->fetchColumn();

//En impro
$impro = $pdo->query("SELECT COUNT(*) FROM Vehiculo WHERE idEstado = 3")->fetchColumn();

//Limpios
$limpios = $pdo->query("SELECT COUNT(*) FROM Vehiculo WHERE idEstado = 1")->fetchColumn();

//Sucios
$sucios = $pdo->query("SELECT COUNT(*) FROM Vehiculo WHERE idEstado = 2")->fetchColumn();

//En ventas
$ventas = $pdo->query("SELECT COUNT(*) FROM Vehiculo WHERE idEstado = 4")->fetchColumn();

//Vendidos
$vendidos = $pdo->query("SELECT COUNT(*) FROM Vehiculo WHERE idEstado = 6")->fetchColumn();

//Revisiones vencidas
$revisionesVencidas = $pdo->query("
    SELECT COUNT(*) 
    FROM Vehiculo 
    WHERE fechaProximaRevision < CURDATE()
")->fetchColumn();




$stmt = $pdo->query("SELECT idEstado, nombreEstado FROM EstadoVehiculo");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);


$vehiculos = obtenerFlotaFiltrada($_GET);
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
                <ul class="navbar-nav ms-auto">
                    <?php if (getUserRole() === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold text-warning" href="<?= volverSegunRol() ?>">
                                Volver a Dashboard Admin
                            </a>
                        </li>
                    <?php endif; ?>
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

        <h1 class="mb-4">Dashboard Mecánico</h1>

        <!-- Resumen General -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Resumen General</h5>

                <div class="row g-3">

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Total flota</h6>
                                <span class="fs-3 fw-bold"><?= $totalFlota ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Alquilados</h6>
                                <span class="fs-3 fw-bold"><?= $alquilados ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>En impro</h6>
                                <span class="fs-3 fw-bold"><?= $impro ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Limpios</h6>
                                <span class="fs-3 fw-bold"><?= $limpios ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Sucios</h6>
                                <span class="fs-3 fw-bold"><?= $sucios ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>En ventas</h6>
                                <span class="fs-3 fw-bold"><?= $ventas ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Vendidos</h6>
                                <span class="fs-3 fw-bold"><?= $vendidos ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center border border-danger">
                            <div class="card-body">
                                <h6 class="text-danger">Revisiones vencidas</h6>
                                <span class="fs-3 fw-bold text-danger"><?= $revisionesVencidas ?></span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <!-- Vehiculos -->
        <div class="card mb-4" id="vehiculos">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Gestión de Vehículos</h5>
                    <div>

                        <button class="btn btn-primary" onclick="location.href='subirFotoVehiculo.php'">
                            Subir Fotos
                        </button>
                        <button class="btn btn-primary" onclick="location.href='darAltaVehiculo.php'">
                            Añadir Vehículo
                        </button>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Consultar Flota</h5>

                        <!-- FILTROS -->
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
                                <button class="btn btn-primary">Filtrar</button>
                            </div>

                            <div class="col-auto">
                                <a href="<?= volverSegunRol() . '#vehiculos' ?>" class="btn btn-danger">
                                    Quitar filtro
                                </a>
                            </div>

                        </form>

                        <!-- TABLA -->
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Matrícula</th>
                                        <th>Marca / Modelo</th>
                                        <th>Estado</th>
                                        <th>Kms</th>
                                        <th>Categoría</th>
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
                                                <?php if ($testeoPagina): ?>
                                                    <a href="editarVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary mb-2">Editar</a>
                                                    <a href="venderVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary mb-2">Vender</a>
                                                    <a href="historialVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary mb-2">Ver Historial</a>
                                                    <button class="btn btn-sm btn-danger mb-2" onclick="confirmarBaja('<?= $row['idVehiculo'] ?>')">
                                                        Dar de baja
                                                    </button>
                                                <?php else: ?>
                                                    <?php if ($row['nombreEstado'] !== 'BAJA' && $row['nombreEstado'] !== 'VENDIDO'): ?>
                                                        <a href="editarVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary mb-2">Editar</a>
                                                    <?php endif; ?>

                                                    <?php if ($row['nombreEstado'] !== 'VENTAS' && $row['nombreEstado'] !== 'BAJA' && $row['nombreEstado'] !== 'VENDIDO'): ?>
                                                        <a href="venderVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary mb-2">Vender</a>
                                                    <?php endif; ?>

                                                    <?php if ($row['nombreEstado'] !== 'BAJA' && $row['nombreEstado'] !== 'VENDIDO'): ?>
                                                        <button class="btn btn-sm btn-danger mb-2" onclick="confirmarBaja('<?= $row['idVehiculo'] ?>')">
                                                            Dar de baja
                                                        </button>
                                                    <?php endif; ?>

                                                    <a href="historialVehiculo.php?idVehiculo=<?= $row['idVehiculo'] ?>" class="btn btn-sm btn-secondary mb-2">Ver Historial</a>
                                                <?php endif; ?>
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

    <script src="../js/vehiculos.js"></script>
</body>

</html>