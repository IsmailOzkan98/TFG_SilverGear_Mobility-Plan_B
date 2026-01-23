<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico', 'limpieza', 'dropoff']);

$pdo = getPDO();
$usuarioSesion = $_SESSION['usuario'];
$dniUsuario = $usuarioSesion['dni'];

if (isset($_GET['success'], $_GET['id'])) {
    $idPagado = (int)$_GET['id'];
    $stmt = $pdo->prepare("
        UPDATE Penalizacion 
        SET estadoPenalizacion = 'PAGADO' 
        WHERE idPenalizacion = :id
    ");
    $stmt->execute([':id' => $idPagado]);

    // Redirigir para limpiar la URL y evitar re-ejecución
    header("Location: penalizacion.php");
    exit;
}

// Obtener todas las penalizaciones del usuario
$stmt = $pdo->prepare("
    SELECT p.idPenalizacion, p.matriculaVehiculo, p.idReserva, p.cantidad, p.nota, p.fechaRegistro, p.estadoPenalizacion
    FROM Penalizacion p
    WHERE p.dniCliente = :dni
    ORDER BY p.fechaRegistro DESC
");
$stmt->execute([':dni' => $dniUsuario]);
$penalizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Penalizaciones</title>
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
    <section class="bgblock" style="background-image: url('../images/backgroundPenalizacion.jpg'); max-width: 1400px;">
        <div class="bgblock-content d-flex flex-column align-items-center" style="min-height: 600px; gap: 30px;">
            <div class="glass w-100" style="max-width: 900px; padding: 30px;">
                <h1 class="display-5 fw-bold mb-3 text-center">Mis Penalizaciones</h1>

                <?php if (empty($penalizaciones)): ?>
                    <p class="text-center text-muted">No tienes penalizaciones.</p>
                    <div class="text-center mt-4">
                        <a href="miPerfil.php" class="btn btn-custom">Volver a mi perfil</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Vehículo</th>
                                    <th>Reserva #</th>
                                    <th>Cantidad (€)</th>
                                    <th>Nota</th>
                                    <th>Fecha registro</th>
                                    <th>Estado / Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($penalizaciones as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['matriculaVehiculo']) ?></td>
                                        <td><?= $p['idReserva'] ?></td>
                                        <td><?= number_format($p['cantidad'], 2) ?> €</td>
                                        <td><?= htmlspecialchars($p['nota']) ?></td>
                                        <td><?= $p['fechaRegistro'] ?></td>
                                        <td>
                                            <?php if ($p['estadoPenalizacion'] === 'PENDIENTE'): ?>
                                                <button class="btn btn-sm btn-custom w-100" data-id="<?= $p['idPenalizacion'] ?>">
                                                    Pagar
                                                </button>
                                            <?php else: ?>
                                                <span class="btn btn-sm btn-custom w-100">
                                                    PAGADO
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe('<?= STRIPE_PUBLISHABLE_KEY ?>');

        document.querySelectorAll('.btn-pagar').forEach(btn => {
            btn.addEventListener('click', () => {
                const idPenalizacion = btn.dataset.id;

                fetch('../includes/crear_pago_penalizacion.php?id=' + idPenalizacion)
                    .then(res => res.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        stripe.redirectToCheckout({
                            sessionId: data.id
                        });
                    })
                    .catch(err => console.error(err));
            });
        });
    </script>
</body>

</html>