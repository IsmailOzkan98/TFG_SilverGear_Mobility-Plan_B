<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente']);

$pdo = getPDO();

$usuarioSesion = $_SESSION['usuario'];

if ($usuarioSesion['rol'] === 'cliente') {
    $idUsuario = $usuarioSesion['idUsuario'];
} else {
    if (!isset($_GET['idUsuario'])) {
        die("Usuario no especificado.");
    }
    $idUsuario = intval($_GET['idUsuario']);
}


// Obtener datos del usuario a eliminar
$stmt = $pdo->prepare("SELECT nombre, apellidos, dni FROM Usuario WHERE idUsuario = ?");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Procesar formulario de confirmación
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmacion = $_POST['confirmacion'] ?? '';
    if ($confirmacion === 'CONFIRMAR') {
        header("Location: ../includes/eliminarUser.php?idUsuario=$idUsuario");
        exit;
    } else {
        $error = "Debes escribir CONFIRMAR para continuar con la eliminación.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Confirmar Eliminación</title>
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

    <section class="bgblock" style="background-image: url('../images/backgroundEliminar.jpg'); max-width: 1400px;">
        <div class="bgblock-content d-flex flex-column align-items-center" style="min-height: 500px; gap: 30px;">

            <div class="glass" style="max-width: 500px; width: 100%; padding: 30px; text-align: center;">

                <h1 class="display-5 fw-bold mb-3 text-center">Confirmar Eliminación</h1>

                <p class="fs-5 mb-4">
                    Vas a eliminar al usuario: <br>
                    <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></strong><br>
                    DNI: <?= htmlspecialchars($usuario['dni']) ?>
                </p>

                <?php if ($error): ?>
                    <p class="text-danger mb-3"><?= $error ?></p>
                <?php endif; ?>

                <form method="POST" class="d-flex flex-column gap-2">
                    <input type="text" name="confirmacion" placeholder="Escribe CONFIRMAR para continuar" class="form-control text-center">
                    <button type="submit" class="btn btn-custom">Eliminar Usuario</button>
                    <a href="<?= volverSegunRol() ?>" class="btn btn-custom">Cancelar</a>
                </form>

            </div>

        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>