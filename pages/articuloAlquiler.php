<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico', 'limpieza', 'dropoff']);

$pdo = getPDO();

// Obtener fecha de carnet
$fechaCarnet = null;

if (isset($_SESSION['usuario']['fechaCarnet'])) {
    $fechaCarnet = $_SESSION['usuario']['fechaCarnet'];
} elseif (isset($_SESSION['usuario']['idUsuario'])) {
    $stmt = $pdo->prepare("SELECT fechaCarnet FROM Usuario WHERE idUsuario = :idUsuario LIMIT 1");
    $stmt->execute([':idUsuario' => $_SESSION['usuario']['idUsuario']]);
    $fechaCarnet = $stmt->fetchColumn();
}

$aplicaRecargoCarnetJoven = false;
if ($fechaCarnet) {
    $fechaCarnetDT = new DateTime($fechaCarnet);
    $haceDosAnios = (new DateTime())->sub(new DateInterval('P2Y'));
    $aplicaRecargoCarnetJoven = $fechaCarnetDT > $haceDosAnios;
}


// Obtener ID del vehículo
$idVehiculo = $_GET['idVehiculo'] ?? null;
if (!$idVehiculo) {
    header("Location: tiendaAlquiler.php");
    exit;
}

// Obtener datos del vehículo
$stmt = $pdo->prepare("
    SELECT v.*, c.nombreCategoria, c.precioBase, c.incrementoSeguro, c.recargoCarnetJoven,
           c.descuentoDia1_3, c.descuentoDia4_6, c.descuentoDia7_10, 
           c.descuentoDia11_19, c.descuentoDia20_mas,
           e.nombreEstado
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

// Obtener imagene
$imagenPrincipal = $vehiculo['imagenPrincipal'] ?? null;
if (!$imagenPrincipal) {
    $imagenPrincipal = '../images/default-car.jpg';
} else {
    $imagenPrincipal = '../images/vehiculos/' . $imagenPrincipal;
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Vehículo – Alquiler</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

            <!-- Imagen principal -->
            <div class="col-12 col-md-6 mb-4">
                <img src="<?= htmlspecialchars($imagenPrincipal) ?>" class="d-block w-100 slider-img" alt="Imagen del vehiculo">
            </div>


            <!-- Información y reserva -->
            <div class="col-12 col-md-6">
                <div class="glass p-4">
                    <h2 class="mb-3"><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h2>
                    <p><strong>Marca:</strong> <?= htmlspecialchars($vehiculo['marca']) ?></p>
                    <p><strong>Modelo:</strong> <?= htmlspecialchars($vehiculo['modelo']) ?></p>
                    <p><strong>Año:</strong> <?= htmlspecialchars($vehiculo['anio']) ?></p>
                    <p><strong>Precio base/día:</strong> <?= number_format($vehiculo['precioBase'], 2) ?> €</p>
                    <p><strong>Descripción:</strong> <?= nl2br(htmlspecialchars($vehiculo['notasInternas'] ?? 'No hay descripción disponible')) ?></p>

                    <?php if ($vehiculo['disponibilidad']): ?>
                        <form id="formReserva" class="mt-4" method="POST" action="../includes/crear_reserva.php">
                            <input type="hidden" name="idVehiculo" value="<?= $vehiculo['idVehiculo'] ?>">

                            <!-- Mensaje Error -->
                            <?php if (!empty($_SESSION['error_reserva'])): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($_SESSION['error_reserva']) ?>
                                </div>
                                <?php unset($_SESSION['error_reserva']); ?>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Fecha inicio</label>
                                <input type="date" class="form-control" name="fechaInicio" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fecha fin</label>
                                <input type="date" class="form-control" name="fechaFin" required>
                            </div>

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" value="1" id="seguro" name="seguro">
                                <label class="form-check-label" for="seguro">
                                    Seguro todo riesgo (+<?= $vehiculo['incrementoSeguro'] ?>%)
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="hidden" name="carnetJoven" value="<?= $aplicaRecargoCarnetJoven ? 1 : 0 ?>">
                                <input class="form-check-input" type="checkbox" id="carnetJoven"
                                    <?= $aplicaRecargoCarnetJoven ? 'checked' : '' ?> disabled>
                                <label class="form-check-label" for="carnetJoven">
                                    Recargo por carnet &lt;2 años (+<?= $vehiculo['recargoCarnetJoven'] ?>%)
                                </label>
                            </div>

                            <input type="hidden" name="idCategoria" value="<?= htmlspecialchars($vehiculo['idCategoria']) ?>">
                            <input type="hidden" name="marcaSolicitada" value="<?= htmlspecialchars($vehiculo['marca']) ?>">
                            <input type="hidden" name="modeloSolicitado" value="<?= htmlspecialchars($vehiculo['modelo']) ?>">
                            <input type="hidden" name="precioDia" id="inputPrecioDia" value="<?= number_format($vehiculo['precioBase'], 2) ?>">
                            <input type="hidden" name="precioTotal" id="inputPrecioTotal" value="<?= number_format($vehiculo['precioBase'], 2) ?>">



                            <p><strong>Duración alquiler:</strong> <span id="duracionAlquiler">1</span> días</p>
                            <p><strong>Descuento por duración:</strong> <span id="descuentoDias">0</span>%</p>
                            <p><strong>Precio final/día:</strong> <span id="precioDia"><?= number_format($vehiculo['precioBase'], 2) ?></span> €</p>
                            <p><strong>Precio total:</strong> <span id="precioTotal"><?= number_format($vehiculo['precioBase'], 2) ?></span> €</p>

                            <button type="submit" class="btn btn-custom w-100">Crear reserva</button>
                        </form>
                    <?php else: ?>
                        <p class="fw-bold text-danger">No disponible para alquiler</p>
                    <?php endif; ?>

                    <a href="tiendaAlquiler.php" class="btn btn-custom w-100 mt-2">Volver a la tienda</a>
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
    <script>
        const form = document.getElementById('formReserva');
        const precioDiaEl = document.getElementById('precioDia');
        const precioTotalEl = document.getElementById('precioTotal');
        const duracionEl = document.getElementById('duracionAlquiler');
        const descuentoEl = document.getElementById('descuentoDias');

        const precioBase = <?= $vehiculo['precioBase'] ?>;
        const incrementoSeguro = <?= $vehiculo['incrementoSeguro'] ?>;
        const recargoCarnetJoven = <?= $vehiculo['recargoCarnetJoven'] ?>;

        function obtenerDescuentoPorDias(dias) {
            if (dias >= 20) return <?= $vehiculo['descuentoDia20_mas'] ?? 0 ?>;
            if (dias >= 11) return <?= $vehiculo['descuentoDia11_19'] ?? 0 ?>;
            if (dias >= 7) return <?= $vehiculo['descuentoDia7_10'] ?? 0 ?>;
            if (dias >= 4) return <?= $vehiculo['descuentoDia4_6'] ?? 0 ?>;
            return <?= $vehiculo['descuentoDia1_3'] ?? 0 ?>;
        }

        function actualizarPrecio() {
            const seguro = form.seguro.checked ? incrementoSeguro : 0;
            const carnet = form.carnetJoven.checked ? recargoCarnetJoven : 0;

            const fechaInicio = new Date(form.fechaInicio.value);
            const fechaFin = new Date(form.fechaFin.value);
            let dias = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24) + 1;
            if (isNaN(dias) || dias < 1) dias = 1;

            const descuento = obtenerDescuentoPorDias(dias);

            let precioDia = precioBase * (1 + seguro / 100 + carnet / 100) * (1 - descuento / 100);
            let precioTotal = precioDia * dias;

            // Actualizar el texto visible
            precioDiaEl.textContent = precioDia.toFixed(2);
            precioTotalEl.textContent = precioTotal.toFixed(2);
            duracionEl.textContent = dias;
            descuentoEl.textContent = descuento;

            // Actualizar los hidden inputs para enviar al POST
            document.getElementById('inputPrecioDia').value = precioDia.toFixed(2);
            document.getElementById('inputPrecioTotal').value = precioTotal.toFixed(2);
        }


        // Actualizar al cambiar cualquiera de los inputs
        form.fechaInicio.addEventListener('change', actualizarPrecio);
        form.fechaFin.addEventListener('change', actualizarPrecio);
        form.seguro.addEventListener('change', actualizarPrecio);
        form.carnetJoven.addEventListener('change', actualizarPrecio);

        // Inicializar precios al cargar
        actualizarPrecio();
    </script>

</body>

</html>