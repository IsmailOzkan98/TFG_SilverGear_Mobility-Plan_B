<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
require_once '../includes/usuario.php';

requireRole(['admin', 'cliente', 'ventas', 'mecanico', 'limpieza', 'dropoff']);

$pdo = getPDO();

if (!isset($_GET['dni'])) {
    die('Usuario no especificado');
}

$dni = $_GET['dni'];

//Consulta para obtener datos de usuario
$stmt = $pdo->prepare("SELECT * FROM Usuario WHERE dni = :dni");
$stmt->execute([':dni' => $dni]);
$usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuarioDB) {
    die('Usuario no encontrado');
}

//manejo de errores
$errores = $_SESSION['errores_editar'] ?? [];
$old = $_SESSION['old_input'] ?? [];

unset($_SESSION['errores_editar'], $_SESSION['old_input']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = new Usuario($_POST, $pdo, false);
    $usuario->dni = $dni;

    $resultado = $usuario->actualizar();

    if (isset($resultado['exito'])) {

        $_SESSION['mensaje'] = 'Usuario ha sido actualizado correctamente';

        unset($_SESSION['errores_editar']);

        header("Location: /pages/editarPerfil.php?dni=" . urlencode($dni));
        exit;
    }

    $_SESSION['errores_editar'] = $resultado['errores'];
    $_SESSION['old_input'] = $_POST;

    header("Location: /pages/editarPerfil.php?dni=" . urlencode($dni));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | SilverGear Mobility</title>

    <!-- Tipografía -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Estilos propios -->
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

    <div id="main-container">
        <section class="bgblock" style="background-image: url('../images/backgorundRegister.jpg'); max-width: 1400px;">
            <div class="bgblock-content d-flex justify-content-center align-items-center" style="min-height: 600px;">

                <div class="glass" style="max-width: 900px; width: 100%;">

                    <h1 class="display-5 fw-bold mb-4 text-center">Editar usuario</h1>

                    <?php if (!empty($_SESSION['mensaje'])): ?>
                        <div class="alert alert-success text-center">
                            <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        </div>
                        <?php unset($_SESSION['mensaje']); ?>
                    <?php endif; ?>

                    <?php if (!empty($errores['general'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($errores['general']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">

                        <!-- Rol -->
                        <?php if ($_SESSION['usuario']['rol'] !== 'cliente'): ?>
                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($_SESSION['usuario']['rol']) ?>"
                                    readonly>
                            </div>

                            <input type="hidden" name="idRol" value="<?= $usuarioDB['idRol'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="idRol" value="<?= $usuarioDB['idRol'] ?>">
                        <?php endif; ?>



                        <!-- DNI -->
                        <div class="col-md-6">
                            <label class="form-label">DNI</label>
                            <input type="text" class="form-control"
                                value="<?= htmlspecialchars($old['dni'] ?? $usuarioDB['dni']) ?>" readonly>

                            <?php if (!empty($errores['dni'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['dni']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Nombre -->
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control"
                                value="<?= htmlspecialchars($old['nombre'] ?? $usuarioDB['nombre']) ?>" required>

                            <?php if (!empty($errores['nombre'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['nombre']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Apellidos -->
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control"
                                value="<?= htmlspecialchars($old['apellidos'] ?? $usuarioDB['apellidos']) ?>" required>

                            <?php if (!empty($errores['apellidos'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['apellidos']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Fecha nacimiento -->
                        <div class="col-md-6">
                            <label class="form-label">Fecha nacimiento</label>
                            <input type="date" name="fechaNacimiento" class="form-control"
                                value="<?= htmlspecialchars($old['fechaNacimiento'] ?? $usuarioDB['fechaNacimiento']) ?>" required>

                            <?php if (!empty($errores['fechaNacimiento'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['fechaNacimiento']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Sexo -->
                        <div class="col-md-6">
                            <label class="form-label">Sexo</label>
                            <select name="sexo" class="form-control">
                                <option value="">Seleccionar</option>
                                <?php foreach (['Masculino', 'Femenino', 'Otro'] as $sexo): ?>
                                    <option value="<?= $sexo ?>" <?= $usuarioDB['sexo'] === $sexo ? 'selected' : '' ?>>
                                        <?= $sexo ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Telefono -->
                        <div class="col-md-6">
                            <label class="form-label">Telefono</label>
                            <input type="text" name="telefono" class="form-control"
                                value="<?= htmlspecialchars($old['telefono'] ?? $usuarioDB['telefono']) ?>" required>

                            <?php if (!empty($errores['telefono'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['telefono']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($old['email'] ?? $usuarioDB['email']) ?>" required>

                            <?php if (!empty($errores['email'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['email']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Direccion -->
                        <div class="col-md-6">
                            <label class="form-label">Direccion</label>
                            <input type="text" name="direccion" class="form-control"
                                value="<?= htmlspecialchars($old['direccion'] ?? $usuarioDB['direccion']) ?>">

                            <?php if (!empty($errores['direccion'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['direccion']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Ciudad -->
                        <div class="col-md-4">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control"
                                value="<?= htmlspecialchars($old['ciudad'] ?? $usuarioDB['ciudad']) ?>">

                            <?php if (!empty($errores['ciudad'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['ciudad']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Pais -->
                        <div class="col-md-4">
                            <label class="form-label">Pais</label>
                            <input type="text" name="pais" class="form-control"
                                value="<?= htmlspecialchars($old['pais'] ?? $usuarioDB['pais']) ?>">

                            <?php if (!empty($errores['pais'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['pais']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Codigo Pòstal -->
                        <div class="col-md-4">
                            <label class="form-label">Codigo postal</label>
                            <input type="text" name="codigoPostal" class="form-control"
                                value="<?= htmlspecialchars($old['codigoPostal'] ?? $usuarioDB['codigoPostal']) ?>">

                            <?php if (!empty($errores['codigoPostal'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['codigoPostal']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Fecha carnet -->
                        <div class="col-md-6">
                            <label class="form-label">Fecha carnet</label>
                            <input type="date" name="fechaCarnet" class="form-control"
                                value="<?= htmlspecialchars($old['fechaCarnet'] ?? $usuarioDB['fechaCarnet']) ?>" required>

                            <?php if (!empty($errores['fechaCarnet'])): ?>
                                <span class="error" style="color:red">
                                    <?= htmlspecialchars($errores['fechaCarnet']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- botones -->
                        <div class="col-12 text-center mt-4">
                            <a href="miPerfil.php" class="btn btn-custom">Volver</a>
                            <button type="submit" class="btn btn-custom">Guardar cambios</button>
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