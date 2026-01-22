<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico']);

$pdo = getPDO();

$idReserva = $_GET['id'] ?? null;
if (!$idReserva) {
    header("Location: miPerfil.php");
    exit;
}

//Obtener datos
$stmt = $pdo->prepare("
    SELECT r.*, u.nombre, u.apellidos, u.dni,
           v.marca AS marcaVehiculo, v.modelo AS modeloVehiculo,
           c.nombreCategoria, c.precioBase, c.incrementoSeguro, c.recargoCarnetJoven,
           c.descuentoDia1_3, c.descuentoDia4_6, c.descuentoDia7_10, c.descuentoDia11_19, c.descuentoDia20_mas
    FROM Reserva r
    JOIN Usuario u ON r.idUsuario = u.idUsuario
    LEFT JOIN Vehiculo v ON r.idVehiculo = v.idVehiculo
    LEFT JOIN Categoria c ON (r.idCategoria = c.idCategoria OR v.idCategoria = c.idCategoria)
    WHERE r.idReserva = :idReserva
    LIMIT 1
");
$stmt->execute([':idReserva' => $idReserva]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reserva) {
    die("Reserva no encontrada");
}

//carnet joven
$fechaCarnet = null;
if (!empty($reserva['idUsuario'])) {
    $stmt = $pdo->prepare("SELECT fechaCarnet FROM Usuario WHERE idUsuario = :idUsuario LIMIT 1");
    $stmt->execute([':idUsuario' => $reserva['idUsuario']]);
    $fechaCarnet = $stmt->fetchColumn();
}


$aplicaRecargoCarnetJoven = false;
if ($fechaCarnet) {
    $fechaCarnetDT = new DateTime($fechaCarnet);
    $haceDosAnios = (new DateTime())->sub(new DateInterval('P2Y'));
    $aplicaRecargoCarnetJoven = $fechaCarnetDT > $haceDosAnios;
}



//permisos
$idUsuario = $_SESSION['usuario']['idUsuario'];
if ($reserva['idUsuario'] != $idUsuario && !in_array($_SESSION['usuario']['rol'], ['admin', 'ventas', 'mecanico'])) {
    die("No tienes permiso para editar esta reserva.");
}

//actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin    = $_POST['fechaFin'];
    $precioDia   = $_POST['precioDia'];
    $precioTotal = $_POST['precioTotal'];
    $seguro      = isset($_POST['seguro']) ? 1 : 0;
    $carnetJoven = isset($_POST['carnetJoven']) ? 1 : 0;

    $stmt = $pdo->prepare("
        UPDATE Reserva SET
            fechaInicio = :fechaInicio,
            fechaFin = :fechaFin,
            precioDia = :precioDia,
            precioTotal = :precioTotal,
            seguro = :seguro,
            carnetJoven = :carnetJoven
        WHERE idReserva = :idReserva
    ");

    $stmt->execute([
        ':fechaInicio' => $fechaInicio,
        ':fechaFin'    => $fechaFin,
        ':precioDia'   => $precioDia,
        ':precioTotal' => $precioTotal,
        ':seguro'      => $seguro,
        ':carnetJoven' => $carnetJoven,
        ':idReserva'   => $idReserva
    ]);

    header("Location: " . volverSegunRol());
    exit;
}


$precioBase       = $reserva['precioBase'] ?? 0;
$incrementoSeguro = $reserva['incrementoSeguro'] ?? 0;
$recargoCarnet    = $reserva['recargoCarnetJoven'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Reserva #<?= $reserva['idReserva'] ?></title>
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

    <section class="bgblock" style="background-image: url('../images/backgorundEditarReserva.jpg'); min-height: 600px;">
        <div class="bgblock-content d-flex flex-column align-items-center" style="gap: 30px; padding: 50px 0;">
            <div class="glass" style="max-width: 800px; width: 100%; padding: 30px;">
                <h1 class="display-5 fw-bold mb-3 text-center">Editar Reserva #<?= $reserva['idReserva'] ?></h1>

                <p><strong>Cliente:</strong> <?= htmlspecialchars($reserva['nombre'] . ' ' . $reserva['apellidos']) ?></p>
                <p><strong>DNI:</strong> <?= htmlspecialchars($reserva['dni']) ?></p>
                <p><strong>Vehículo:</strong> <?= htmlspecialchars($reserva['marcaVehiculo'] ?? $reserva['marcaSolicitada'] . ' ' . $reserva['modeloVehiculo'] ?? $reserva['modeloSolicitado']) ?></p>
                <p><strong>Categoría:</strong> <?= htmlspecialchars($reserva['nombreCategoria'] ?? 'Sin categoría') ?></p>

                <form id="formReserva" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fechaInicio" class="form-control" value="<?= $reserva['fechaInicio'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fechaFin" class="form-control" value="<?= $reserva['fechaFin'] ?>" required>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="seguro" id="seguro" <?= $reserva['seguro'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="seguro">Seguro todo riesgo (+<?= $incrementoSeguro ?>%)</label>
                    </div>

                    <div class="form-check mb-3">
                        <input type="hidden" name="carnetJoven" value="<?= $aplicaRecargoCarnetJoven ? 1 : 0 ?>">
                        <input class="form-check-input" type="checkbox" id="carnetJoven" <?= $aplicaRecargoCarnetJoven ? 'checked' : '' ?> disabled>
                        <label class="form-check-label" for="carnetJoven">
                            Recargo por carnet &lt;2 años (+<?= $recargoCarnet ?>%)
                        </label>
                    </div>


                    <input type="hidden" name="precioDia" id="inputPrecioDia" value="<?= $reserva['precioDia'] ?>">
                    <input type="hidden" name="precioTotal" id="inputPrecioTotal" value="<?= $reserva['precioTotal'] ?>">

                    <p><strong>Precio/día:</strong> <span id="precioDia"><?= number_format($reserva['precioDia'], 2) ?></span> €</p>
                    <p><strong>Precio total:</strong> <span id="precioTotal"><?= number_format($reserva['precioTotal'], 2) ?></span> €</p>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-custom">Actualizar Reserva</button>
                        <a href="<?= volverSegunRol() ?>" class="btn btn-custom">Cancelar</a>

                    </div>
                </form>
            </div>
        </div>
    </section>

    <script>
        const form = document.getElementById('formReserva');
        const precioDiaEl = document.getElementById('precioDia');
        const precioTotalEl = document.getElementById('precioTotal');
        const inputPrecioDia = document.getElementById('inputPrecioDia');
        const inputPrecioTotal = document.getElementById('inputPrecioTotal');

        const precioBase = <?= $precioBase ?>;
        const incrementoSeguro = <?= $incrementoSeguro ?>;
        const recargoCarnet = <?= $aplicaRecargoCarnetJoven ? $recargoCarnet : 0 ?>;

        function actualizarPrecio() {
            const seguro = form.seguro.checked ? incrementoSeguro : 0;
            let precioDia = precioBase * (1 + seguro / 100 + recargoCarnet / 100);

            const fechaInicio = new Date(form.fechaInicio.value);
            const fechaFin = new Date(form.fechaFin.value);
            let dias = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24) + 1;
            if (isNaN(dias) || dias < 1) dias = 1;

            let precioTotal = precioDia * dias;

            precioDiaEl.textContent = precioDia.toFixed(2);
            precioTotalEl.textContent = precioTotal.toFixed(2);

            inputPrecioDia.value = precioDia.toFixed(2);
            inputPrecioTotal.value = precioTotal.toFixed(2);
        }


        form.fechaInicio.addEventListener('change', actualizarPrecio);
        form.fechaFin.addEventListener('change', actualizarPrecio);
        form.seguro.addEventListener('change', actualizarPrecio);

        actualizarPrecio();
    </script>
</body>

</html>