<?php
require_once 'includes/common.php';
require_once 'includes/security.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SilverGear Mobility</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet"> <!-- Tipografias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Favicon -->
    <?php imprimirFavicon(); ?>

</head>

<body>
    <!-- Contenedores -->

    <!-- Header -->
    <div id="header-container">
        <header class="py-3" style="background: var(--c-light); color: var(--c-dark);">
            <nav class="navbar navbar-expand-lg">
                <div class="container">

                    <div class="d-flex align-items-center gap-3">

                        <a class="logo" href="#">
                            <img src="images/Logo-500x500T.png" alt="SilverGear Mobility Logo" height="60">
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
                                <a href="index.php" class="nav-link me-3 mb-1">Home</a>
                            </li>

                            <?php if (!isset($_SESSION['usuario'])): ?>
                                <li class="nav-item">
                                    <a href="pages/login.php" class="nav-link me-3 mb-1">Login</a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a href="../pages/miPerfil.php" class="nav-link me-3 mb-1">Mi Perfil</a>
                                </li>
                                <li class="nav-item">
                                    <a href="pages/tiendaAlquiler.php" class="nav-link me-3 mb-1">Alquilar</a>
                                </li>
                                <li class="nav-item">
                                    <a href="pages/tiendaComprar.php" class="nav-link me-3 mb-1">Comprar</a>
                                </li>
                                <li class="nav-item">
                                    <a href="pages/cesta.php" class="nav-link me-3 mb-1">🛒</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link me-3 mb-1" href="includes/logout.php">Log Out</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>
            </nav>
        </header>
        <div class="divider"></div>

    </div>

    <div id="main-container">
        <div id="main-container">

            <section class="bgblock" style="background-image: url('images/backgroundIndex.jpg');">
                <div class="bgblock-content text-center">
                    <h1 class="display-5 fw-bold mb-3">Bienvenido a SilverGear Mobility</h1>
                    <p class="lead mb-4">
                        Tu plataforma confiable para alquilar o comprar el vehículo que necesitas.
                    </p>
                    <a href="pages/login.php" class="btn btn-custom mt-3">Iniciar</a>
                </div>
            </section>

            <section class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-lg-8">

                        <p class="mb-4 fs-5">
                            En <strong>SilverGear Mobility</strong> queremos que moverte sea más fácil que nunca.
                            Aquí podrás encontrar vehículos disponibles para <strong>alquiler</strong> cuando los necesites,
                            ya sea para un viaje puntual, trabajo o una escapada espontánea.
                        </p>

                        <p class="mb-4 fs-5">
                            Si estás pensando en adquirir tu próximo vehículo, también contamos con una selección de modelos
                            disponibles para <strong>comprar</strong>, con información clara y un proceso sencillo para ayudarte
                            a tomar la mejor decisión.
                        </p>

                        <p class="fs-5">
                            Explora nuestras opciones, compara y elige cómo quieres moverte.
                            En <strong>SilverGear Mobility</strong>, tu camino comienza aquí.
                        </p>

                    </div>
                </div>
            </section>


        </div>

    </div>
    <!-- <div id="extra-container">
        <div class="divider"></div>

        <div id="multiCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">

                <div class="carousel-item active">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0A-Economy.png" class="slider-img" alt="A-Economy">
                            <label>A-Economy</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0B-Compact.png" class="slider-img" alt="B-Compact">
                            <label>B-Compact</label>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0C-Intermediate.png" class="slider-img" alt="C-Intermediate">
                            <label>C-Intermediate</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0D-SUV.png" class="slider-img" alt="D-SUV">
                            <label>D-SUV</label>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0E-Premium.png" class="slider-img" alt="E-Premium">
                            <label>E-Premium</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0F-Van.png" class="slider-img" alt="F-Van">
                            <label>F-Van</label>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0G-Cargo.png" class="slider-img" alt="G-Cargo">
                            <label>G-Cargo</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="images/0H-Classic.png" class="slider-img" alt="H-Classic">
                            <label>H-Classic</label>
                        </div>
                    </div>
                </div>

            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#multiCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#multiCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
    <div class="divider"></div> -->

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
                        <a href="index.php" class="nav-link me-3 mb-1">Home</a>
                        <a href="pages/tiendaAlquiler.php" class="nav-link me-3 mb-1">Alquilar</a>
                        <a href="pages/tiendaComprar.php" class="nav-link me-3 mb-1">Comprar</a>
                        <a href="pages/politicaPrivacidad.php" class="nav-link mb-1">Política de privacidad</a>
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


    <!-- JS loader -->
    <!-- <script src="js/loader.js"></script> -->
    <!-- <script src="js/registro.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para showcase -->
    <!--  <script src="js/showcase.js"></script>  -->

</body>



</html>