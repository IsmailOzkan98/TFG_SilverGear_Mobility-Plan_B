<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin']);

$pdo = getPDO();

// Empleados
$stmt = $pdo->query("SELECT u.*, r.nombreRol FROM Usuario u JOIN Rol r ON u.idRol = r.idRol WHERE r.nombreRol != 'cliente'");
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Clientes
$stmt = $pdo->query("SELECT u.* FROM Usuario u JOIN Rol r ON u.idRol = r.idRol WHERE r.nombreRol = 'cliente'");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vehículos
$stmt = $pdo->query("SELECT idEstado, nombreEstado FROM EstadoVehiculo");
$estados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$vehiculos = obtenerFlotaFiltrada($_GET);



// Reservas
$stmt = $pdo->query("SELECT r.*, u.nombre, u.apellidos, v.marca, v.modelo FROM Reserva r
                     JOIN Usuario u ON r.idUsuario = u.idUsuario
                     JOIN Vehiculo v ON r.idVehiculo = v.idVehiculo");
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Reservas activas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM Reserva WHERE estadoReserva='Activa'");
$activas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Administrador - SilverGear Mobility</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<script src="../js/vehiculos.js"></script>

<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility</span>

            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboardMecanico.php">Panel Mecanico</a></li>
                    <li class="nav-item"><a class="nav-link" href="#empleados">Empleados</a></li>
                    <li class="nav-item"><a class="nav-link" href="#clientes">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#vehiculos">Vehículos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#reservas">Reservas</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-4">

        <h1 class="mb-4">Dashboard Administrador</h1>

        <!-- Resumen General -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Resumen General</h5>

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Total Empleados</h6>
                                <span class="fs-3 fw-bold"><?= count($empleados) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Total Clientes</h6>
                                <span class="fs-3 fw-bold"><?= count($clientes) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Vehículos</h6>
                                <span class="fs-3 fw-bold"><?= count($vehiculos) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h6>Reservas Activas</h6>
                                <span class="fs-3 fw-bold"><?= $activas ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empleados -->
        <div class="card mb-4" id="empleados">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Gestión de Empleados</h5>
                    <button class="btn btn-primary">Añadir Empleado</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empleados as $row): ?>
                                <tr>
                                    <td><?= $row['dni'] ?></td>
                                    <td><?= $row['nombre'] . ' ' . $row['apellidos'] ?></td>
                                    <td><?= $row['email'] ?></td>
                                    <td><?= $row['nombreRol'] ?></td>
                                    <td>
                                        <a href="editarUsuario.php?dni=<?= urlencode($row['dni']) ?>" class="btn btn-sm btn-secondary">Editar</a>
                                        <button class="btn btn-sm btn-danger">Eliminar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Clientes -->
        <div class="card mb-4" id="clientes">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Gestión de Clientes</h5>
                    <button class="btn btn-primary">Añadir Cliente</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $row): ?>
                                <tr>
                                    <td><?= $row['dni'] ?></td>
                                    <td><?= $row['nombre'] . ' ' . $row['apellidos'] ?></td>
                                    <td><?= $row['email'] ?></td>
                                    <td>
                                        <a href="editarUsuario.php?dni=<?= urlencode($row['dni']) ?>" class="btn btn-sm btn-secondary">Editar</a>
                                        <button class="btn btn-sm btn-danger">Eliminar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                                <a href="dashboardAdmin.php#vehiculos" class="btn btn-danger">
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

        <!-- Reservas -->
        <div class="card mb-4" id="reservas">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5>Gestión de Reservas</h5>
                    <button class="btn btn-primary">Crear Reserva</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Vehículo</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Precio</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservas as $row): ?>
                                <tr>
                                    <td><?= $row['idReserva'] ?></td>
                                    <td><?= $row['nombre'] . ' ' . $row['apellidos'] ?></td>
                                    <td><?= $row['marca'] . ' ' . $row['modelo'] ?></td>
                                    <td><?= $row['fechaInicio'] ?></td>
                                    <td><?= $row['fechaFin'] ?></td>
                                    <td><?= $row['precioTotal'] ?>€</td>
                                    <td><?= $row['estadoReserva'] ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary">Editar</button>
                                        <button class="btn btn-sm btn-danger">Cancelar</button>
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