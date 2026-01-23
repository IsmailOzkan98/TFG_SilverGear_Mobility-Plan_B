<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico', 'limpieza', 'dropoff']);

$pdo = getPDO();

// Construir filtros
$where = ["v.disponibilidad = 1", "e.nombreEstado IN ('LIMPIO','SUCIO')"];
$params = [];

if (!empty($_GET['marca'])) {
    $where[] = "v.marca LIKE :marca";
    $params[':marca'] = "%" . $_GET['marca'] . "%";
}

if (!empty($_GET['modelo'])) {
    $where[] = "v.modelo LIKE :modelo";
    $params[':modelo'] = "%" . $_GET['modelo'] . "%";
}

if (!empty($_GET['categoria'])) {
    $where[] = "v.idCategoria = :categoria";
    $params[':categoria'] = $_GET['categoria'];
}

if (!empty($_GET['precio_min'])) {
    $where[] = "c.precioBase >= :precio_min";
    $params[':precio_min'] = $_GET['precio_min'];
}

if (!empty($_GET['precio_max'])) {
    $where[] = "c.precioBase <= :precio_max";
    $params[':precio_max'] = $_GET['precio_max'];
}

// Consulta principal
$sql = "
SELECT 
    v.*,
    c.nombreCategoria,
    c.precioBase,
    e.nombreEstado,
    v.imagenPrincipal
FROM Vehiculo v
JOIN Categoria c ON v.idCategoria = c.idCategoria
JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.precioBase ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar vh por marca y modelo
$vehiculosUnicos = [];
foreach ($vehiculos as $v) {
    $key = $v['marca'] . '|' . $v['modelo'];
    if (!isset($vehiculosUnicos[$key])) {
        $vehiculosUnicos[$key] = $v;
    }
}
$vehiculos = array_values($vehiculosUnicos);


//filtros
$marcas = $pdo->query("SELECT DISTINCT marca FROM Vehiculo ORDER BY marca")->fetchAll(PDO::FETCH_COLUMN);

if (!empty($_GET['marca'])) {
    $stmtModelos = $pdo->prepare("SELECT DISTINCT modelo FROM Vehiculo WHERE marca = :marca ORDER BY modelo");
    $stmtModelos->execute([':marca' => $_GET['marca']]);
    $modelosFiltro = $stmtModelos->fetchAll(PDO::FETCH_COLUMN);
} else {
    $modelosFiltro = $pdo->query("SELECT DISTINCT modelo FROM Vehiculo ORDER BY modelo")->fetchAll(PDO::FETCH_COLUMN);
}


$categorias = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria ORDER BY nombreCategoria")->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tienda Alquiler</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

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
        <div class="container">
            <div class="container-fluid mt-4">
                <div class="row">

                    <!-- FILTROS -->
                    <div class="col-12 col-md-3 mb-3">
                        <div class="glass p-3" style="min-height: 100%;">

                            <h4 class="mb-3" style="font-family: 'Rajdhani'; letter-spacing: 3px;">
                                FILTROS
                            </h4>

                            <form method="GET">

                                <!-- Marca -->
                                <div class="mb-3">
                                    <label class="form-label">Marca</label>
                                    <select class="form-select" name="marca">
                                        <option value="">Seleccione</option>
                                        <?php foreach ($marcas as $m): ?>
                                            <option value="<?= htmlspecialchars($m) ?>"
                                                <?= ($_GET['marca'] ?? '') == $m ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($m) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Modelo -->
                                <div class="mb-3">
                                    <label class="form-label">Modelo</label>
                                    <select class="form-select" name="modelo">
                                        <option value="">Seleccione</option>
                                        <?php foreach ($modelosFiltro as $mod): ?>
                                            <option value="<?= htmlspecialchars($mod) ?>"
                                                <?= ($_GET['modelo'] ?? '') == $mod ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($mod) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Categoría -->
                                <div class="mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="categoria">
                                        <option value="">Seleccione</option>
                                        <?php foreach ($categorias as $c): ?>
                                            <option value="<?= $c['idCategoria'] ?>"
                                                <?= ($_GET['categoria'] ?? '') == $c['idCategoria'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['nombreCategoria']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Precio -->
                                <div class="mb-3">
                                    <label class="form-label">Precio / día (€)</label>
                                    <div class="d-flex gap-2">
                                        <input type="number"
                                            class="form-control"
                                            name="precio_min"
                                            placeholder="Min"
                                            value="<?= $_GET['precio_min'] ?? '' ?>">
                                        <input type="number"
                                            class="form-control"
                                            name="precio_max"
                                            placeholder="Max"
                                            value="<?= $_GET['precio_max'] ?? '' ?>">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-custom w-100 mb-2">
                                    Aplicar filtros
                                </button>

                                <a href="tiendaAlquiler.php" class="btn btn-custom w-100">
                                    Quitar filtros
                                </a>

                            </form>
                        </div>
                    </div>

                    <!-- LISTADO -->
                    <div class="col-12 col-md-9">
                        <div class="row g-4">
                            <?php foreach ($vehiculos as $v): ?>
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="card h-100 vehicle-card">
                                        <!-- Imagen con ratio fijo -->
                                        <div class="ratio vehicle-img-ratio" style="--bs-aspect-ratio: 80%;">
                                            <img src="<?= isset($v['imagenPrincipal']) ? '../images/vehiculos/' . htmlspecialchars($v['imagenPrincipal']) : '../images/default-car.jpg' ?>"
                                                class="img-fluid object-fit-cover"
                                                alt="Imagen de <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>">
                                        </div>

                                        <!-- Contenido de la card -->
                                        <div class="card-body">
                                            <h6 class="card-title mb-1"><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></h6>
                                            <p class="mb-1"><?= htmlspecialchars($v['nombreCategoria']) ?></p>
                                            <p class="fw-bold mb-0">Desde <?= number_format($v['precioBase'], 2) ?>€ / día</p>

                                            <?php if ($v['disponibilidad']): ?>
                                                <a href="articuloAlquiler.php?idVehiculo=<?= $v['idVehiculo'] ?>" class="btn btn-custom w-100 mt-2">Ver Detalles</a>
                                            <?php else: ?>
                                                <p class="text-danger fw-bold mt-2">NO DISPONIBLE</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

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

            </div>
        </div>




</body>

</html>