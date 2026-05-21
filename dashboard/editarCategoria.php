<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin']);

$pdo = getPDO();

$id = $_GET['idCategoria'] ?? null;
if (!$id) die("Categoría no válida");

// obtiene categoria 
$stmt = $pdo->prepare("SELECT * FROM Categoria WHERE idCategoria = ?");
$stmt->execute([$id]);
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoria) die("Categoría no encontrada");

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

    //validacion 
    if ($data['nombreCategoria'] === '') {
        $errores['nombre'] = "El nombre es obligatorio.";
    }

    if (empty($errores)) {
        editarCategoria($pdo, $id, $data);
        $mensaje = "Categoría actualizada correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php imprimirFavicon(); ?>
    <meta charset="UTF-8">
    <title>Editar Categoria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">SilverGear Mobility - Editar Categoria</span>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?= volverSegunRol() ?>#categorias">Volver</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container">

    <div class="card">
        <div class="card-body">

            <h1 class="mb-4">Editar Categorioa</h1>

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
                        <label>Nombre</label>
                        <input class="form-control" name="nombreCategoria"
                               value="<?= htmlspecialchars($categoria['nombreCategoria']) ?>">
                    </div>

                    <div class="col-md-6">
                        <label>Precio base</label>
                        <input class="form-control" name="precioBase"
                               value="<?= $categoria['precioBase'] ?>">
                    </div>

                    <div class="col-12">
                        <label>Descripcion</label>
                        <textarea class="form-control" name="descripcion"><?= htmlspecialchars($categoria['descripcion']) ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label>Seguro (%)</label>
                        <input class="form-control" name="incrementoSeguro"
                               value="<?= $categoria['incrementoSeguro'] ?>">
                    </div>

                    <div class="col-md-4">
                        <label>Carnet joven (%)</label>
                        <input class="form-control" name="recargoCarnetJoven"
                               value="<?= $categoria['recargoCarnetJoven'] ?>">
                    </div>

                    <hr>

                    <div class="col-md-4"><input class="form-control" name="descuentoDia1_3" value="<?= $categoria['descuentoDia1_3'] ?>"></div>
                    <div class="col-md-4"><input class="form-control" name="descuentoDia4_6" value="<?= $categoria['descuentoDia4_6'] ?>"></div>
                    <div class="col-md-4"><input class="form-control" name="descuentoDia7_10" value="<?= $categoria['descuentoDia7_10'] ?>"></div>
                    <div class="col-md-6"><input class="form-control" name="descuentoDia11_19" value="<?= $categoria['descuentoDia11_19'] ?>"></div>
                    <div class="col-md-6"><input class="form-control" name="descuentoDia20_mas" value="<?= $categoria['descuentoDia20_mas'] ?>"></div>

                    <div class="col-12 mt-3">
                        <button class="btn btn-primary">Guardar cambios</button>
                        <a href="<?= volverSegunRol() ?>#categorias" class="btn btn-secondary">Volver</a>
                    </div>

                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>