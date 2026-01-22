<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico', 'ventas', 'dropoff', 'limpieza', 'cliente']);

$pdo = getPDO();

if (!isset($_GET['dni'])) {
    die('Usuario no especificado');
}

$dni = $_GET['dni'];

$stmt = $pdo->prepare("SELECT * FROM Usuario WHERE dni = :dni");
$stmt->execute([':dni' => $dni]);
$usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtRoles = $pdo->query("SELECT idRol, nombreRol FROM Rol ORDER BY nombreRol");
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

if (!$usuarioDB) {
    die('Usuario no encontrado');
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE Usuario SET
                nombre = :nombre,
                apellidos = :apellidos,
                fechaNacimiento = :fechaNacimiento,
                sexo = :sexo,
                direccion = :direccion,
                ciudad = :ciudad,
                pais = :pais,
                codigoPostal = :codigoPostal,
                telefono = :telefono,
                email = :email,
                fechaCarnet = :fechaCarnet,
                idRol = :idRol
            WHERE dni = :dni
        ");

        $stmt->execute([
            ':nombre' => $_POST['nombre'],
            ':apellidos' => $_POST['apellidos'],
            ':fechaNacimiento' => $_POST['fechaNacimiento'],
            ':sexo' => $_POST['sexo'],
            ':direccion' => $_POST['direccion'],
            ':ciudad' => $_POST['ciudad'],
            ':pais' => $_POST['pais'],
            ':codigoPostal' => $_POST['codigoPostal'],
            ':telefono' => $_POST['telefono'],
            ':email' => $_POST['email'],
            ':fechaCarnet' => $_POST['fechaCarnet'],
            ':idRol' => $_POST['idRol'],
            ':dni' => $dni
        ]);

        $_SESSION['mensaje'] = 'Usuario actualizado correctamente';
        header('Location: miPerfil.php');
        exit;
    } catch (PDOException $e) {
        $errores['general'] = $e->getMessage();
    }
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
                                value="<?= htmlspecialchars($usuarioDB['dni']) ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['nombre']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['apellidos']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha nacimiento</label>
                            <input type="date" name="fechaNacimiento" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['fechaNacimiento']) ?>" required>
                        </div>

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

                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['telefono']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['email']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['direccion']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Ciudad</label>
                            <input type="text" name="ciudad" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['ciudad']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">País</label>
                            <input type="text" name="pais" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['pais']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Código postal</label>
                            <input type="text" name="codigoPostal" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['codigoPostal']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha carnet</label>
                            <input type="date" name="fechaCarnet" class="form-control"
                                value="<?= htmlspecialchars($usuarioDB['fechaCarnet']) ?>" required>
                        </div>

                        <div class="col-12 text-center mt-4">
                            <a href="miPerfil.php" class="btn btn-custom">Volver</a>
                            <button type="submit" class="btn btn-custom">Guardar cambios</button>
                        </div>

                    </form>
                </div>

            </div>
        </section>
    </div>

    <div id="footer-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>