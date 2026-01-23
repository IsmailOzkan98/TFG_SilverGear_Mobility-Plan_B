<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico', 'limpieza', 'dropoff']);


$pdo = getPDO();


$usuarioSesion = $_SESSION['usuario'];
$idUsuario = $usuarioSesion['idUsuario'];

$stmt = $pdo->prepare("
    SELECT nombre, apellidos, dni
    FROM Usuario
    WHERE idUsuario = ?
");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die('Usuario no encontrado');
}

// Reservas pendientes
$stmt = $pdo->prepare("
    SELECT r.idReserva, r.fechaInicio, r.fechaFin, r.estado,
           r.marcaSolicitada, r.modeloSolicitado,
           c.nombreCategoria
    FROM Reserva r
    LEFT JOIN Categoria c ON r.idCategoria = c.idCategoria
    WHERE r.idUsuario = ?
      AND r.estado IN ('NO CUBIERTA', 'CUBIERTA')
    ORDER BY r.fechaInicio DESC
");
$stmt->execute([$idUsuario]);
$reservasPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Historial
$stmt = $pdo->prepare("
    SELECT r.idReserva, r.fechaInicio, r.fechaFin, r.estado,
           r.marcaSolicitada, r.modeloSolicitado, r.idCategoria AS categoriaSolicitada,
           v.marca AS marcaVehiculo, v.modelo AS modeloVehiculo,
           c.nombreCategoria AS categoriaVehiculo
    FROM Reserva r
    LEFT JOIN Vehiculo v ON r.idVehiculoAsignado = v.idVehiculo
    LEFT JOIN Categoria c ON v.idCategoria = c.idCategoria
    WHERE r.idUsuario = ?
      AND r.estado IN ('FINALIZADO', 'CANCELADA')
    ORDER BY r.fechaInicio DESC
");


$stmt->execute([$idUsuario]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);






$stmt = $pdo->prepare("
    SELECT c.idCompra, v.marca, v.modelo, c.precio, c.fechaCompra
    FROM Compra c
    JOIN Vehiculo v ON c.idVehiculo = v.idVehiculo
    WHERE c.idUsuario = ?
    ORDER BY c.fechaCompra DESC
");
$stmt->execute([$idUsuario]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>

    <!-- Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <!-- Header -->
    <div id="header-container">
        <header class="py-3" style="background: var(--c-light); color: var(--c-dark);">
            <nav class="navbar navbar-expand-lg">
                <div class="container">

                    <div class="d-flex align-items-center gap-3">

                        <a class="logo" href="#">
                            <img src="../images/Logo-500x500T.png" alt="SilverGear Mobility Logo" height="60">
                        </a>

                        <?php if ($weather): ?>
                            <div class="d-flex align-items-center px-2 py-1 weather-block">
                                <img src="https://openweathermap.org/img/wn/<?= $weather['icon'] ?>.png" alt="Icono del clima">
                                <span class="ms-1 fw-bold">
                                    <?= $weather['city'] ?> · <?= $weather['temp'] ?>°C
                                </span>
                            </div>
                        <?php endif; ?>

                    </div>



                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                        <ul class="navbar-nav text-center">

                            <?php
                            $rolesDashboard = ['admin', 'ventas', 'limpieza', 'dropoff', 'mecanico'];
                            $rol = getUserRole();
                            ?>

                            <?php if (in_array($rol, $rolesDashboard)): ?>


                                <li class="nav-item">
                                    <a class="nav-link fw-bold text-warning" href="<?= volverSegunRol() ?>">
                                        Volver a Dashboard <?= ucfirst($rol) ?>
                                    </a>
                                </li>

                            <?php endif; ?>
                            <li class="nav-item">
                                <a href="../index.php" class="nav-link me-3 mb-1">Home</a>
                            </li>

                            <?php if (!isset($_SESSION['usuario'])): ?>
                                <li class="nav-item">
                                    <a href="login.php" class="nav-link me-3 mb-1">Login</a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a href="miPerfil.php" class="nav-link me-3 mb-1">Mi Perfil</a>
                                </li>
                                <li class="nav-item">
                                    <a href="tiendaAlquiler.php" class="nav-link me-3 mb-1">Alquilar</a>
                                </li>
                                <li class="nav-item">
                                    <a href="tiendaComprar.php" class="nav-link me-3 mb-1">Comprar</a>
                                </li>
                                <li class="nav-item">
                                    <a href="cesta.php" class="nav-link me-3 mb-1">🛒</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link me-3 mb-1" href="../includes/logout.php">Log Out</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>
            </nav>
        </header>
        <div class="divider"></div>

    </div>

    <section class="bgblock" style="background-image: url('../images/backgorundMiPerfil.png'); max-width: 1400px;">
        <div class="bgblock-content d-flex flex-column align-items-center" style="min-height: 600px; gap: 30px;">

            <div class="glass" style="max-width: 900px; width: 100%; padding: 30px;">

                <h1 class="display-5 fw-bold mb-3 text-center">Mi Perfil</h1>

                <p class="text-center fs-4 fw-semibold mb-1">
                    <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>
                </p>

                <p class="text-center text-muted mb-4">
                    DNI: <?= htmlspecialchars($usuario['dni']) ?>
                </p>

                <div class="d-flex justify-content-center gap-3 mb-5">
                    <a href="tiendaAlquiler.php" class="btn btn-custom">Alquilar Vehiculo</a>
                    <a href="tiendaComprar.php" class="btn btn-custom">Comprar Vehiculo</a>
                    <a href="editarPerfil.php?dni=<?= urlencode($usuario['dni']) ?>" class="btn btn-custom">Editar perfil</a>
                    <a href="cesta.php" class="btn btn-custom">Mi Cesta</a>
                </div>

                <div class="d-flex justify-content-center gap-3 mb-5">
                    <a href="penalizacion.php" class="btn btn-custom">Penalizaciones</a>

                    <?php if ($_SESSION['usuario']['rol'] === 'cliente'): ?>
                        <div>
                            <a href="confirmarEliminarUsuario.php" class="btn btn-danger">
                                Eliminar mi cuenta
                            </a>
                        </div>

                    <?php endif; ?>
                </div>


                <h3 class="mb-3">Mis reservas</h3>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group mb-4">
                        <?php if (empty($reservasPendientes)): ?>
                            <li class="list-group-item text-center text-muted">
                                No tienes reservas pendientes
                            </li>
                        <?php else: ?>
                            <?php foreach ($reservasPendientes as $r): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            Reserva #<?= $r['idReserva'] ?> —
                                            <?= $r['fechaInicio'] ?> / <?= $r['fechaFin'] ?> —
                                            <?= $r['estado'] ?> —
                                            Marca: <?= htmlspecialchars($r['marcaVehiculo'] ?? $r['marcaSolicitada']) ?> —
                                            Modelo: <?= htmlspecialchars($r['modeloVehiculo'] ?? $r['modeloSolicitado']) ?> —
                                            Categoría: <?= htmlspecialchars($r['nombreCategoria'] ?? 'Sin categoría') ?>
                                        </span>
                                        <div class="d-flex gap-2">
                                            <a href="editarReserva.php?id=<?= $r['idReserva'] ?>" class="btn btn-sm btn-custom">
                                                Editar
                                            </a>
                                            <form method="POST" action="../includes/cancelarReserva.php" style="display:inline">
                                                <input type="hidden" name="idReserva" value="<?= $r['idReserva'] ?>">
                                                <button type="submit" class="btn btn-sm btn-custom">Cancelar</button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <h3 class="mb-3">Mi historial de reservas</h3>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group mb-3">
                        <?php if (empty($historial)): ?>
                            <li class="list-group-item text-center text-muted">
                                No hay reservas anteriores
                            </li>
                        <?php else: ?>
                            <?php foreach ($historial as $h): ?>
                                <li class="list-group-item">
                                    Reserva #<?= $h['idReserva'] ?> —
                                    <?= $h['fechaInicio'] ?> / <?= $h['fechaFin'] ?> —
                                    <?= $h['estado'] ?> —
                                    Marca: <?= htmlspecialchars($h['marcaVehiculo'] ?: $h['marcaSolicitada'] ?: 'N/A') ?> —
                                    Modelo: <?= htmlspecialchars($h['modeloVehiculo'] ?: $h['modeloSolicitado'] ?: 'N/A') ?> —
                                    Categoría: <?= htmlspecialchars($h['categoriaVehiculo'] ?: ($h['categoriaSolicitada'] ? getNombreCategoria($h['categoriaSolicitada']) : 'Sin categoría')) ?>

                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <h3 class="mb-3">Mis compras realizadas</h3>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group mb-4">
                        <?php if (empty($compras)): ?>
                            <li class="list-group-item text-center text-muted">
                                No has comprado ningún vehículo
                            </li>
                        <?php else: ?>
                            <?php foreach ($compras as $compra): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($compra['marca'] . ' ' . $compra['modelo']) ?> —
                                    <?= number_format($compra['precio'], 2) ?> € —
                                    <?= $compra['fechaCompra'] ?>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="pt-5 pb-3" style="background: var(--c-light); color: var(--c-dark);">
        <div class="container">
            <div class="row">

                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Contacto</h5>
                    <p>Carretera Torrellano-Aeropuerto, CV-852, km 1.5, 03320 Alicante</p>
                    <p>Tel: +34 123 456 789</p>
                    <p>Email: info@silvergearmobility.com</p>
                    <p>Horario: Lunes - Domingo, 7:00 - 23:00</p>
                </div>

                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Enlaces</h5>
                    <nav class="d-flex flex-wrap">
                        <a href="../index.php" class="nav-link me-3 mb-1">Home</a>
                        <a href="tiendaAlquiler.php" class="nav-link me-3 mb-1">Alquilar</a>
                        <a href="tiendaComprar.php" class="nav-link me-3 mb-1">Comprar</a>
                        <a href="politicaPrivacidad.php" class="nav-link mb-1">Política de privacidad</a>
                    </nav>
                </div>

                <div class="col-md-4 mb-4">
                    <h5 class="footer-title">Siguenos</h5>
                    <div class="d-flex gap-3 mb-3">
                        <a href="https://www.facebook.com" class="footer-icon"><i class="bi bi-facebook"></i></a>
                        <a href="https://www.instagram.com" class="footer-icon"><i class="bi bi-instagram"></i></a>
                        <a href="https://www.twitter.com" class="footer-icon"><i class="bi bi-twitter"></i></a>
                        <a href="https://www.linkedin.com" class="footer-icon"><i class="bi bi-linkedin"></i></a>
                    </div>
                    <p class="mb-2">Suscríbete a nuestro boletín para recibir ofertas exclusivas.</p>
                    <form class="d-flex" role="form">
                        <input type="email" class="form-control me-2" placeholder="Tu correo">
                        <button type="submit" class="btn btn-custom">Suscribirse</button>
                    </form>
                </div>
            </div>

            <hr style="border-top:1px solid var(--c-silver); margin:2rem 0 1rem 0;">
            <div class="text-center">
                <p class="mb-0">&copy; 2025 SilverGear Mobility. Sistema de reserva activado.</p>
            </div>
        </div>
    </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>