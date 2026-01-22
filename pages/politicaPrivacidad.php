<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad - SilverGear Mobility</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Estilos CSS -->
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

    <!-- Contenido principal -->
    <main class="container py-5">
        <h1 class="mb-4">Política de Privacidad</h1>
        <p>En <strong>SilverGear Mobility</strong>, nos comprometemos a proteger la privacidad de nuestros usuarios.
            Esta política describe cómo recopilamos, usamos y protegemos la información personal proporcionada a través de
            nuestra plataforma de alquiler y compra de vehículos.</p>

        <h3 class="mt-4">1. Información que recopilamos</h3>
        <p>Recopilamos información cuando los usuarios se registran en nuestra plataforma o utilizan nuestros servicios.
            Esto puede incluir:</p>
        <ul>
            <li>Nombre y apellidos</li>
            <li>Dirección de correo electrónico</li>
            <li>Teléfono de contacto</li>
            <li>Información relacionada con la reserva o compra de vehículos</li>
        </ul>

        <h3 class="mt-4">2. Uso de la información</h3>
        <p>La información recopilada se utiliza únicamente con fines de:</p>
        <ul>
            <li>Gestionar reservas y compras de vehículos</li>
            <li>Mejorar la experiencia del usuario en la plataforma</li>
            <li>Enviar notificaciones relevantes sobre servicios y promociones (solo si el usuario ha dado su consentimiento)</li>
        </ul>

        <h3 class="mt-4">3. Protección de datos</h3>
        <p>Implementamos medidas de seguridad técnicas y organizativas para proteger los datos personales de los usuarios contra
            accesos no autorizados, pérdida o divulgación.</p>

        <h3 class="mt-4">4. Compartición de información</h3>
        <p>No compartimos datos personales con terceros, excepto cuando sea necesario para cumplir con la ley o para
            gestionar reservas a través de socios de transporte autorizados.</p>

        <h3 class="mt-4">5. Consentimiento y derechos del usuario</h3>
        <p>Al utilizar nuestro sitio web, los usuarios consienten la recopilación y el uso de sus datos según lo descrito
            en esta política. Los usuarios tienen derecho a:</p>
        <ul>
            <li>Acceder a sus datos personales</li>
            <li>Solicitar correcciones o eliminación de datos</li>
            <li>Retirar su consentimiento en cualquier momento</li>
        </ul>

        <h3 class="mt-4">6. Cambios en la política</h3>
        <p>Podemos actualizar esta política de privacidad ocasionalmente. Se recomienda a los usuarios revisar esta página
            periódicamente para mantenerse informados sobre cómo protegemos su información.</p>

        <p class="mt-4"><strong>Fecha de última actualización:</strong> 22 de enero de 2026</p>
    </main>

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
