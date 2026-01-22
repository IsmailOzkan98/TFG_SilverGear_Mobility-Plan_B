<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
require_once '../includes/Vehiculo.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico']);

$pdo = getPDO();


if (!isset($_SESSION['cesta'])) {
    $_SESSION['cesta'] = [];
}

//Exito o fail STRIPE
if (isset($_GET['success'])) {
    $procesado = true;

    $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado='VENDIDO'");
    $stmtEstado->execute();
    $idVendido = $stmtEstado->fetchColumn();

    if ($idVendido) {
        foreach ($_SESSION['cesta'] as $idVehiculo => $_dummy) {
            $stmt = $pdo->prepare("SELECT * FROM Vehiculo WHERE idVehiculo = :idVehiculo");
            $stmt->execute([':idVehiculo' => $idVehiculo]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($datos) {
                $vehiculo = new Vehiculo($datos, $pdo);

                $dniTrabajador = $_SESSION['usuario']['dni'] ?? null;
                cambiarEstadoVehiculo(
                    $pdo,
                    $vehiculo,
                    $idVendido,
                    $dniTrabajador,
                    'Vehiculo ha sido vendido'
                );

                $stmtCompra = $pdo->prepare("
                    INSERT INTO Compra (idUsuario, idVehiculo, precio) 
                    VALUES (:idUsuario, :idVehiculo, :precio)
                ");
                $stmtCompra->execute([
                    ':idUsuario' => $_SESSION['usuario']['idUsuario'],
                    ':idVehiculo' => $idVehiculo,
                    ':precio' => $vehiculo->precioVenta
                ]);
            }
        }
    }

    $_SESSION['cesta'] = [];
}



if (isset($_GET['cancel'])) {
    $cancelado = true;
}

//añadir, quitar, vaciar
$accion = $_GET['accion'] ?? null;
$idVehiculo = $_GET['id'] ?? null;

if ($accion) {
    switch ($accion) {
        case 'añadir':
            if ($idVehiculo) {
                $_SESSION['cesta'][$idVehiculo] = true;
            }
            break;
        case 'quitar':
            if ($idVehiculo && isset($_SESSION['cesta'][$idVehiculo])) {
                unset($_SESSION['cesta'][$idVehiculo]);
            }
            break;
        case 'vaciar':
            $_SESSION['cesta'] = [];
            break;
    }
}

//calcular total
$vehiculosCesta = [];
$total = 0;

if (!empty($_SESSION['cesta'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cesta'])));
    $stmt = $pdo->query("
        SELECT idVehiculo, marca, modelo, precioVenta
        FROM Vehiculo
        WHERE idVehiculo IN ($ids)
    ");
    $vehiculosCesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($vehiculosCesta as $v) {
        $total += $v['precioVenta'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Cesta</title>
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

    <div class="container my-5">
        <h2 class="mb-4">Mi Cesta</h2>


        <?php if (isset($procesado) && $procesado): ?>
            <div class="alert alert-success">¡Compra procesada con éxito!</div>
        <?php endif; ?>
        <?php if (isset($cancelado) && $cancelado): ?>
            <div class="alert alert-warning">Pago cancelado. La cesta no se ha vaciado.</div>
        <?php endif; ?>

        <?php if (empty($vehiculosCesta)): ?>
            <p>Tu cesta está vacía.</p>
            <a href="tiendaComprar.php" class="btn btn-custom">Volver a la tienda</a>
            <a href="miPerfil.php" class="btn btn-custom">Volver a mi perfil</a>
        <?php else: ?>
            <div class="glass p-4">
                <table class="table table-striped mb-3">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Precio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehiculosCesta as $v): ?>
                            <tr>
                                <td><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></td>
                                <td><?= number_format($v['precioVenta'], 2) ?> €</td>
                                <td>
                                    <a href="cesta.php?accion=quitar&id=<?= $v['idVehiculo'] ?>" class="btn btn-custom btn-sm">Quitar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h4>Total: <?= number_format($total, 2) ?> €</h4>

                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <a href="cesta.php?accion=vaciar" class="btn btn-custom">Vaciar cesta</a>
                    <a href="tiendaComprar.php" class="btn btn-custom">Seguir comprando</a>
                    <button id="checkout-button" class="btn btn-custom">
                        Processar Pago
                    </button>
                </div>
            </div>
        <?php endif; ?>
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
        const btn = document.getElementById('checkout-button');
        if (btn) {
            const stripe = Stripe('<?= STRIPE_PUBLISHABLE_KEY ?>');

            btn.addEventListener('click', () => {
                fetch('../includes/crear_pago.php')
                    .then(response => response.json())
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
        }
    </script>
</body>

</html>