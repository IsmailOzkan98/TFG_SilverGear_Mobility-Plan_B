<?php
session_start();
require_once '../includes/common.php';
require_once '../includes/usuario.php';
require_once '../includes/security.php';

$pdo = getPDO();

//consulta para obtener roles
$stmtRoles = $pdo->query("SELECT idRol, nombreRol FROM Rol ORDER BY nombreRol");
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

//para selector de roles
$rolActual = getUserRole();
$isAdmin = ($rolActual === 'admin');


$errores = $_SESSION['errores_registro'] ?? [];
$datos = $_SESSION['datos_registro'] ?? [];
unset($_SESSION['errores_registro'], $_SESSION['datos_registro']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = $_POST;
    $pdo = getPDO();

    try {
        $usuario = new Usuario($datos, $pdo, true);
        $resultado = $usuario->guardar();

        if (isset($resultado['exito']) && $resultado['exito'] === true) {
            $_SESSION['mensaje'] = "Usuario registrado correctamente.";
            header('Location: login.php');
            exit;
        } else {
            $errores = $resultado['errores'];
            $_SESSION['errores_registro'] = $errores;
            $_SESSION['datos_registro'] = $datos;
        }
    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
        $_SESSION['errores_registro'] = $errores;
        $_SESSION['datos_registro'] = $datos;
    }
}
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
    <link rel="stylesheet" href="../css/style.css">

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

    <div id="main-container">
        <section class="bgblock" style="background-image: url('../images/backgorundRegister.jpg'); max-width: 1400px;">
            <div class="bgblock-content d-flex justify-content-center align-items-center" style="min-height: 500px;">
                <div class="glass" style="max-width: 600px; width: 100%;">
                    <h1 class="display-5 fw-bold mb-4 text-center">Registrar</h1>

                    <?php if (!empty($errores['general'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errores['general']) ?></div>
                    <?php endif; ?>

                    <form id="formRegistro" class="row g-3" method="POST">

                        <?php
                        $campos = [
                            'nombre' => 'Nombre',
                            'apellidos' => 'Apellidos',
                            'dni' => 'DNI/NIE',
                            'fechaNacimiento' => 'Fecha Nac.',
                            'sexo' => 'Sexo',
                            'direccion' => 'Dirección',
                            'ciudad' => 'Ciudad',
                            'pais' => 'País',
                            'codigoPostal' => 'C.Postal',
                            'telefono' => 'Teléfono',
                            'email' => 'Email',
                            'fechaCarnet' => 'Fecha Carnet',
                            'contrasena' => 'Contraseña',
                            'repetirContrasena' => 'R. Contraseña'
                        ];

                        $obligatorios = [
                            'nombre',
                            'apellidos',
                            'dni',
                            'fechaNacimiento',
                            'telefono',
                            'email',
                            'fechaCarnet'
                        ];

                        $opcionales = [
                            'direccion',
                            'ciudad',
                            'pais',
                            'codigoPostal'
                        ];
                        ?>

                        <h4 class="text-center mt-2 mb-3">Datos obligatorios</h4>

                        <div class="row g-3">
                            <?php foreach ($obligatorios as $campo): ?>
                                <?php
                                $tipo = in_array($campo, ['fechaNacimiento', 'fechaCarnet']) ? 'date'
                                    : (in_array($campo, ['email']) ? 'email' : 'text');
                                ?>

                                <div class="col-12 d-flex align-items-center">
                                    <label class="form-label me-2" style="min-width:120px;">
                                        <?= $campos[$campo] ?>*
                                    </label>

                                    <div style="width:100%">
                                        <input type="<?= $tipo ?>"
                                            class="form-control"
                                            name="<?= $campo ?>"
                                            value="<?= htmlspecialchars($datos[$campo] ?? '') ?>"
                                            required>

                                        <?php if (!empty($errores[$campo])): ?>
                                            <span class="error" style="color:red">
                                                <?= htmlspecialchars($errores[$campo]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Selector Rol Solo visible para admin -->
                        <?php if ($isAdmin): ?>

                            <div class="row g-3">
                                <div class="col-12 d-flex align-items-center">
                                    <label class="form-label fw-bold text-warning me-2" style="min-width:120px;">-=Rol=-</label>

                                    <div style="width:100%">
                                        <select name="idRol" class="form-select">
                                            <?php foreach ($roles as $rol): ?>
                                                <option value="<?= $rol['idRol'] ?>"
                                                    <?= ($datos['idRol'] ?? 2) == $rol['idRol'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($rol['nombreRol']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>

                            <!-- invitado -->
                            <input type="hidden" name="idRol" value="2">

                        <?php endif; ?>

                        <div class="row g-3">
                            <!-- Contraseñas -->
                            <?php foreach (['contrasena', 'repetirContrasena'] as $campo): ?>
                                <div class="col-12 d-flex align-items-center">
                                    <label for="<?= $campo ?>" class="form-label me-2" style="min-width:120px;">
                                        <?= $campos[$campo] ?>*
                                    </label>

                                    <div style="width:100%">
                                        <input type="password"
                                            class="form-control"
                                            id="<?= $campo ?>"
                                            name="<?= $campo ?>"
                                            required>

                                        <?php if (!empty($errores[$campo])): ?>
                                            <span class="error" style="color:red">
                                                <?= htmlspecialchars($errores[$campo]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <h4 class="text-center mt-4 mb-3">Datos opcionales</h4>

                        <div class="row g-3">
                            <?php foreach ($opcionales as $campo): ?>
                                <div class="col-12 d-flex align-items-center">
                                    <label class="form-label me-2" style="min-width:120px;">
                                        <?= $campos[$campo] ?>
                                    </label>

                                    <div style="width:100%">
                                        <input type="text"
                                            class="form-control"
                                            name="<?= $campo ?>"
                                            value="<?= htmlspecialchars($datos[$campo] ?? '') ?>">

                                        <?php if (!empty($errores[$campo])): ?>
                                            <span class="error" style="color:red">
                                                <?= htmlspecialchars($errores[$campo]) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Sexo -->
                        <div class="col-12 d-flex align-items-center">
                            <label for="sexo" class="form-label me-2" style="min-width:120px;">Sexo</label>
                            <div style="width:100%">
                                <select class="form-control" id="sexo" name="sexo">
                                    <option value="">Selecciona</option>
                                    <option value="Masculino" <?= ($datos['sexo'] ?? '') == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                                    <option value="Femenino" <?= ($datos['sexo'] ?? '') == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                                    <option value="Otro" <?= ($datos['sexo'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                                </select>

                                <?php if (!empty($errores['sexo'])): ?>
                                    <span class="error" style="color:red">
                                        <?= htmlspecialchars($errores['sexo']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-12 text-center mt-3">
                            <a href="login.php" class="btn btn-custom">Ya tengo cuenta</a>
                            <button type="submit" class="btn btn-custom">Enviar</button>
                        </div>

                    </form>
                </div>
            </div>
        </section>
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