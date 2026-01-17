<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico']);

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
</head>

<body>
    <div id="header-container"></div>

    <div class="container mt-4">
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
                                <div class="ratio" style="--bs-aspect-ratio: 80%;">
                                    <img src="<?= $v['imagenPrincipal'] ?? '../images/default-car.jpg' ?>" class="img-fluid object-fit-cover" alt="Imagen de <?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?>">
                                </div>
                                <div class="card-body">
                                    <h6><?= htmlspecialchars($v['marca'] . ' ' . $v['modelo']) ?></h6>
                                    <p><?= htmlspecialchars($v['nombreCategoria']) ?></p>
                                    <p class="fw-bold">Desde <?= number_format($v['precioBase'], 2) ?>€ / día</p>

                                    <?php if ($v['disponibilidad']): ?>
                                        <a href="articuloAlquiler.php?idVehiculo=<?= $v['idVehiculo'] ?>" class="btn btn-custom w-100">Ver Detalles</a>
                                    <?php else: ?>
                                        <p class="text-danger fw-bold">NO DISPONIBLE</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>


                </div>
            </div>

        </div>
    </div>

    <div id="footer-container"></div>
</body>

</html>