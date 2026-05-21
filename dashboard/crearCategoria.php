<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin']);

$pdo = getPDO();

$mensaje = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'nombreCategoria' => trim($_POST['nombreCategoria'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'incrementoSeguro' => $_POST['incrementoSeguro'] ?? null,
        'recargoCarnetJoven' => $_POST['recargoCarnetJoven'] ?? null,
        'precioBase' => $_POST['precioBase'] ?? null,
        'descuentoDia1_3' => $_POST['descuentoDia1_3'] ?? null,
        'descuentoDia4_6' => $_POST['descuentoDia4_6'] ?? null,
        'descuentoDia7_10' => $_POST['descuentoDia7_10'] ?? null,
        'descuentoDia11_19' => $_POST['descuentoDia11_19'] ?? null,
        'descuentoDia20_mas' => $_POST['descuentoDia20_mas'] ?? null,
    ];

    // Validaciones
    if ($data['nombreCategoria'] === '') {
        $errores['nombre'] = "El nombre es obligatorio.";
    }

    if (empty($errores)) {

        try {
            crearCategoria($pdo, $data);
            $mensaje = "Categoría creada correctamente.";
        } catch (Exception $e) {
            $errores['db'] = "Error al crear categoría.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php imprimirFavicon(); ?>
    <meta charset="UTF-8">
    <title>Crear Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">SilverGear Mobility - Categorias</span>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?= volverSegunRol() ?>">Volver</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">

    <div class="card">
        <div class="card-body">

            <h1 class="mb-4">Crear Categoria</h1>

            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <?php if ($errores): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label>Nombre categoria</label>
                        <input class="form-control" name="nombreCategoria" required>
                    </div>

                    <div class="col-md-6">
                        <label>Precio base</label>
                        <input class="form-control" name="precioBase" type="number" step="0.01" required>
                    </div>

                    <div class="col-12">
                        <label>Descripcion</label>
                        <textarea class="form-control" name="descripcion"></textarea>
                    </div>

                    <div class="col-md-4">
                        <label>Incremento seguro (%)</label>
                        <input class="form-control" name="incrementoSeguro" type="number" step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label>Recargo carnet joven (%)</label>
                        <input class="form-control" name="recargoCarnetJoven" type="number" step="0.01">
                    </div>

                    <hr>

                    <div class="col-md-4">
                        <label>Descuento 1-3 dias</label>
                        <input class="form-control" name="descuentoDia1_3" type="number" step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label>Descuento 4-6 días</label>
                        <input class="form-control" name="descuentoDia4_6" type="number" step="0.01">
                    </div>

                    <div class="col-md-4">
                        <label>Descuento 7-10 dias</label>
                        <input class="form-control" name="descuentoDia7_10" type="number" step="0.01">
                    </div>

                    <div class="col-md-6">
                        <label>Descuento 11-19 dias</label>
                        <input class="form-control" name="descuentoDia11_19" type="number" step="0.01">
                    </div>

                    <div class="col-md-6">
                        <label>Descuento 20+ días</label>
                        <input class="form-control" name="descuentoDia20_mas" type="number" step="0.01">
                    </div>

                    <div class="col-12 mt-3">
                        <button class="btn btn-primary">Crear Categoria</button>
                        <a href="<?= volverSegunRol() ?>#categorias" class="btn btn-secondary">Volver</a>
                    </div>

                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>