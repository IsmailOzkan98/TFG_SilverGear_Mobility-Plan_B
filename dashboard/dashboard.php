<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin','mecanico']);

$pdo = getPDO();

// -----------------------
// Consultas
// -----------------------

// Empleados
$stmt = $pdo->query("SELECT u.*, r.nombreRol FROM Usuario u JOIN Rol r ON u.idRol = r.idRol WHERE r.nombreRol != 'cliente'");
$empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Clientes
$stmt = $pdo->query("SELECT u.* FROM Usuario u JOIN Rol r ON u.idRol = r.idRol WHERE r.nombreRol = 'cliente'");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vehículos
$stmt = $pdo->query("SELECT v.*, e.nombreEstado, c.nombreCategoria FROM Vehiculo v 
                     JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
                     JOIN Categoria c ON v.idCategoria = c.idCategoria");
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="../css/workerspace.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="logo"><strong>SilverGear Mobility</strong></div>
    <div class="nav-links">
        <a href="#empleados">Empleados</a>
        <a href="#clientes">Clientes</a>
        <a href="#vehiculos">Vehículos</a>
        <a href="#reservas">Reservas</a>
        <a href="../includes/logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="container">

    <h1>Dashboard Administrador</h1>

    <!-- Estadísticas rápidas -->
    <div class="card">
        <h2>Resumen General</h2>
        <div style="display:flex; gap:20px; flex-wrap: wrap;">
            <div class="card" style="flex:1; min-width:150px;">
                <h3>Total Empleados</h3>
                <p class="highlight"><?= count($empleados) ?></p>
            </div>
            <div class="card" style="flex:1; min-width:150px;">
                <h3>Total Clientes</h3>
                <p class="highlight"><?= count($clientes) ?></p>
            </div>
            <div class="card" style="flex:1; min-width:150px;">
                <h3>Vehículos</h3>
                <p class="highlight"><?= count($vehiculos) ?></p>
            </div>
            <div class="card" style="flex:1; min-width:150px;">
                <h3>Reservas Activas</h3>
                <p class="highlight"><?= $activas ?></p>
            </div>
        </div>
    </div>

    <!-- Gestión de Empleados -->
    <div class="card" id="empleados">
        <h2>Gestión de Empleados</h2>
        <button class="primary">Añadir Empleado</button>
        <table>
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($empleados as $row): ?>
                <tr>
                    <td><?= $row['dni'] ?></td>
                    <td><?= $row['nombre'] . ' ' . $row['apellidos'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['nombreRol'] ?></td>
                    <td>
                        <button class="secondary">Editar</button>
                        <button class="orange">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gestión de Clientes -->
    <div class="card" id="clientes">
        <h2>Gestión de Clientes</h2>
        <button class="primary">Añadir Cliente</button>
        <table>
            <thead>
                <tr>
                    <th>DNI</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clientes as $row): ?>
                <tr>
                    <td><?= $row['dni'] ?></td>
                    <td><?= $row['nombre'] . ' ' . $row['apellidos'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>
                        <button class="secondary">Editar</button>
                        <button class="orange">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gestión de Vehículos -->
    <div class="card" id="vehiculos">
        <h2>Gestión de Vehículos</h2>
        <button class="primary" onclick="window.location.href='darAltaVehiculo.php'">Añadir Vehículo</button>
        <table>
            <thead>
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
                <?php foreach($vehiculos as $row): ?>
                <tr>
                    <td><?= $row['matricula'] ?></td>
                    <td><?= $row['marca'] . ' ' . $row['modelo'] ?></td>
                    <td><span class="badge-orange"><?= $row['nombreEstado'] ?></span></td>
                    <td><?= $row['kmActual'] ?></td>
                    <td><?= $row['disponibilidad'] ? 'Disponible' : 'No disponible' ?></td>
                    <td>
                        <button class="secondary">Editar</button>
                        <button class="orange">Dar de baja</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gestión de Reservas -->
    <div class="card" id="reservas">
        <h2>Gestión de Reservas</h2>
        <button class="primary">Crear Reserva</button>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Vehículo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Precio Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservas as $row): ?>
                <tr>
                    <td><?= $row['idReserva'] ?></td>
                    <td><?= $row['nombre'] . ' ' . $row['apellidos'] ?></td>
                    <td><?= $row['marca'] . ' ' . $row['modelo'] ?></td>
                    <td><?= $row['fechaInicio'] ?></td>
                    <td><?= $row['fechaFin'] ?></td>
                    <td><?= $row['precioTotal'] ?>€</td>
                    <td><?= $row['estadoReserva'] ?></td>
                    <td>
                        <button class="secondary">Editar</button>
                        <button class="orange">Cancelar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>
