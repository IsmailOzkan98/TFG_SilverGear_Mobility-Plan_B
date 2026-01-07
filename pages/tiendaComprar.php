<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'limpieza', 'ventas', 'mecanico', 'dropoff']);

$pdo = getPDO();
$vehiculos = [];

$stmt = $pdo->prepare("
    SELECT v.*, c.nombreCategoria, e.nombreEstado
    FROM Vehiculo v
    JOIN Categoria c ON v.idCategoria = c.idCategoria
    JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
    WHERE e.nombreEstado = 'VENTAS'
");
$stmt->execute();
$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Comprar Vehiculo Segunda Mano</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet"> <!-- Tipografias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <!-- Contenedores -->
    <div id="header-container"></div>
    <div id="main-container">
        <div class="container">
            <div class="container-fluid mt-4">
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <div class="glass p-3" style="min-height: 100%;">

                            <h4 class="mb-3" style="font-family: 'Rajdhani'; letter-spacing: 3px;">
                                FILTROS
                            </h4>

                            <div class="mb-3">
                                <label class="form-label">Ordenar por</label>
                                <select class="form-select" id="ordenamiento">
                                    <option value="">Seleccione</option>
                                    <option value="km">Menos km primero</option>
                                    <option value="precio">Mas barato primero</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Marca</label>
                                <select class="form-select">
                                    <option>Seleccione</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Modelo</label>
                                <select class="form-select">
                                    <option>Seleccione</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-select">
                                    <option>Seleccione</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio</label>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control" placeholder="Min">
                                    <input type="number" class="form-control" placeholder="Max">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kilometraje</label>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control" placeholder="Min">
                                    <input type="number" class="form-control" placeholder="Max">
                                </div>
                            </div>

                            <button class="btn btn-custom w-100">Aplicar filtros</button>
                        </div>
                    </div>

                    <!-- mostrar vehiculos -->
                    <div class="col-12 col-md-9">
                        <div class="row g-4">
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <div class="card h-100 vehicle-card">
                                        <img src="<?= $vehiculo['imagenPrincipal'] ?? '../images/default-car.jpg' ?>" class="card-img-top" alt="Imagen de <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?>">
                                        <div class="card-body">
                                            <h6 class="card-title mb-1"><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></h6>
                                            <p class="mb-1"><?= htmlspecialchars($vehiculo['nombreCategoria']) ?></p>
                                            <p class="fw-bold mb-0"><?= number_format($vehiculo['precioAdquisicion'], 2) ?>€</p>
                                            <p class="fw-bold mb-0"><?= number_format($vehiculo['kmActual']) ?> km</p>
                                            <br>
                                            <?php if ($vehiculo['disponibilidad']): ?>
                                                <button class="btn btn-custom w-100" onclick="loadPage('pages/articuloCompra.php?matricula=<?= urlencode($vehiculo['matricula']) ?>')">Ver Detalles</button>
                                            <?php else: ?>
                                                <p class="fw-bold mb-0 text-danger">NO DISPONIBLE</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    
    </div>
    <div id="extra-container"></div>
    <div id="footer-container"></div>

    <!-- JS loader -->
    <!-- <script src="js/loader.js"></script> -->
    <!-- <script src="js/registro.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para showcase -->
    <!--  <script src="js/showcase.js"></script>  -->

</body>



</html>