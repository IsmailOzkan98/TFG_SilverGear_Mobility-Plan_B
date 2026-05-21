<?php
session_start();
require_once '../includes/common.php';
require_once '../includes/security.php';

$pdo = getPDO();
$errores = [];
$usuario = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if (isset($_POST['validarEmail'])) {

        $email = $_POST['email'] ?? '';

        if (empty($email)) {
            $errores[] = "El email es obligatorio.";
        } else {
            $stmt = $pdo->prepare(
                "SELECT idUsuario, nombre FROM Usuario WHERE email = ?"
            );
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$usuario) {
                $errores[] = "El email no existe.";
            } else {
                $_SESSION['recuperar_id'] = $usuario['idUsuario'];
                $_SESSION['recuperar_nombre'] = $usuario['nombre'];
            }
        }
    }


    if (isset($_POST['cambiarPass'])) {

        $pass = $_POST['contrasena'] ?? '';
        $repetir = $_POST['repetirContrasena'] ?? '';

        $valid = validarContrasena($pass);
        if ($valid !== true) $errores[] = $valid;

        $valid = validarContrasenaRepetida($pass, $repetir);
        if ($valid !== true) $errores[] = $valid;

        if (empty($errores) && isset($_SESSION['recuperar_id'])) {

            $hash = hashContrasena($pass);

            $stmt = $pdo->prepare(
                "UPDATE Usuario SET contrasena = ? WHERE idUsuario = ?"
            );
            $stmt->execute([
                $hash,
                $_SESSION['recuperar_id']
            ]);

            unset($_SESSION['recuperar_id'], $_SESSION['recuperar_nombre']);
            header("Location: login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>

    <!-- Bootstrap -->
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

    <section class="bgblock" style="background-image: url('../images/backgroundRecuperar.jpg'); max-width: 1400px;">
        <div class="bgblock-content d-flex justify-content-center align-items-center" style="min-height: 500px;">

            <div class="glass" style="max-width: 500px; width: 100%;">
                <h1 class="display-5 fw-bold mb-4 text-center">Recuperar Contraseña</h1>

                <!-- ERRORES -->
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errores as $e): ?>
                            <div><?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="d-flex flex-column">

                    <!-- EMAIL -->
                    <?php if (!isset($_SESSION['recuperar_id'])): ?>
                        <input type="email"
                            class="form-control mb-3"
                            name="email"
                            placeholder="Tu email"
                            required>

                        <button type="submit"
                            name="validarEmail"
                            class="btn btn-custom mt-2 mb-3">
                            Validar Email
                        </button>
                    <?php endif; ?>

                    <!-- NUEVA PASS -->
                    <?php if (isset($_SESSION['recuperar_id'])): ?>
                        <h3 class="text-center mb-3">
                            Bienvenido <?= htmlspecialchars($_SESSION['recuperar_nombre']) ?>
                        </h3>

                        <input type="password"
                            class="form-control mb-3"
                            name="contrasena"
                            placeholder="Nueva Contraseña"
                            required>

                        <input type="password"
                            class="form-control mb-3"
                            name="repetirContrasena"
                            placeholder="Repetir Contraseña"
                            required>

                        <button type="submit"
                            name="cambiarPass"
                            class="btn btn-custom mt-2 mb-3">
                            Cambiar Contraseña
                        </button>
                    <?php endif; ?>

                    <button type="button"
                        class="btn btn-custom mt-2"
                        onclick="location.href='login.php'">
                        Volver al Login
                    </button>

                </form>
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