<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico', 'limpieza', 'dropoff']);

$pdo = getPDO();

//Obtener el ID
$idVehiculo = $_GET['idVehiculo'] ?? null;

if (!$idVehiculo) {
    header("Location: tiendaComprar.php");
    exit;
}

//Obtener datos
$stmt = $pdo->prepare("
    SELECT v.*, c.nombreCategoria, e.nombreEstado
    FROM Vehiculo v
    JOIN Categoria c ON v.idCategoria = c.idCategoria
    JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
    WHERE v.idVehiculo = :idVehiculo
    LIMIT 1
");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehiculo) {
    echo "<p>Vehículo no encontrado.</p>";
    exit;
}

//Obtener imagenes
$stmt = $pdo->prepare("
    SELECT rutaImagen 
    FROM Vehiculo_Imagenes 
    WHERE idVehiculo = :idVehiculo
    ORDER BY idImagen ASC
");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$imagenes = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($imagenes)) {
    $imagenes[] = '../images/default-car.jpg';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Vehículo</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">

    <!-- Favicon -->
    <?php imprimirFavicon(); ?>
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

    <div class="container my-5">
        <div class="row">

            <!-- Carrusel -->
            <div class="col-12 col-md-6 mb-4">
                <div id="articuloCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($imagenes as $index => $img): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="<?= htmlspecialchars($img) ?>" class="d-block w-100 slider-img" alt="Imagen <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#articuloCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#articuloCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
            </div>

            <!-- Info de vh -->
            <div class="col-12 col-md-6">
                <div class="glass p-4">
                    <h2 class="mb-3"><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h2>
                    <p class="mb-2"><strong>Marca:</strong> <?= htmlspecialchars($vehiculo['marca']) ?></p>
                    <p class="mb-2"><strong>Modelo:</strong> <?= htmlspecialchars($vehiculo['modelo']) ?></p>
                    <p class="mb-2"><strong>Año:</strong> <?= htmlspecialchars($vehiculo['anio']) ?></p>
                    <p class="mb-2"><strong>Precio:</strong> <?= number_format($vehiculo['precioVenta'], 2) ?> €</p>
                    <p class="mb-3"><strong>Kilometraje:</strong> <?= number_format($vehiculo['kmActual']) ?> km</p>
                    <p class="mb-3"><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($vehiculo['notasInternas'] ?? 'No hay descripción disponible')) ?></p>

                    <?php if ($vehiculo['disponibilidad']): ?>
                        <a class="btn btn-custom w-100 mb-2" href="cesta.php?accion=añadir&id=<?= $vehiculo['idVehiculo'] ?>">Añadir a la cesta</a>
                    <?php else: ?>
                        <p class="fw-bold text-danger mb-2">No disponible para compra</p>
                    <?php endif; ?>

                    <a href="tiendaComprar.php" class="btn btn-custom w-100">Volver a la Tienda</a>
                </div>
            </div>
        </div>
    </div>

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
                        <input type="email" class="form-control me-2" placeholder="Tu correo" disabled>
                        <button type="submit" class="btn btn-custom" disabled>Suscribirse</button>
                    </form>
                    <p>*temporalmente deshabilitado</p>
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