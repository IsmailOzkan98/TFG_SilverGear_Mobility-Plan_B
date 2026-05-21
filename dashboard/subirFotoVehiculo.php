<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico', 'ventas']);
require_once '../includes/Vehiculo.php';

$pdo = getPDO();
$mensaje = '';
$errores = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subirImagen'])) {
    $marca = strtoupper(trim($_POST['marca']));
    $modelo = strtoupper(trim($_POST['modelo']));

    if (!empty($marca) && !empty($modelo) && isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        $allowed = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            $errores[] = "Tipo de archivo no permitido. Solo JPG, JPEG, PNG.";
        } else {
            $nombreArchivo = $marca . '-' . $modelo . '.jpg'; 
            $rutaDestino = "../images/vehiculos/" . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                $stmt = $pdo->prepare("UPDATE Vehiculo SET imagenPrincipal = :imagen WHERE UPPER(marca) = :marca AND UPPER(modelo) = :modelo");
                $stmt->execute([
                    ':imagen' => $nombreArchivo,
                    ':marca' => $marca,
                    ':modelo' => $modelo
                ]);
                $mensaje = "Imagen subida y vehículos actualizados correctamente.";
            } else {
                $errores[] = "Error al mover el archivo.";
            }
        }
    } else {
        $errores[] = "Rellena todos los campos y selecciona una imagen.";
    }
}

//marcas 
$marcas = $pdo->query("SELECT DISTINCT marca FROM Vehiculo ORDER BY marca")->fetchAll(PDO::FETCH_COLUMN);

//modelos por marca
$allModelos = [];

//vehiculos sin estados
$estadosExcluidos = ["VENTAS","VENDIDO","BAJA"];
$placeholders = implode(',', array_fill(0, count($estadosExcluidos), '?'));

//consulta JOIN
$stmtModelos = $pdo->prepare("
    SELECT v.marca, v.modelo
    FROM Vehiculo v
    JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
    WHERE e.nombreEstado NOT IN ($placeholders)
    ORDER BY v.marca, v.modelo
");
$stmtModelos->execute($estadosExcluidos);

$allModelos = [];
foreach ($stmtModelos->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = strtoupper($row['modelo']);
    if (!isset($allModelos[$row['marca']][$key])) {
        $allModelos[$row['marca']][$key] = $row['modelo'];
    }
}


foreach ($stmtModelos->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = strtoupper($row['modelo']);
    if (!isset($allModelos[$row['marca']][$key])) {
        $allModelos[$row['marca']][$key] = $row['modelo'];
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subida masiva de imágenes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php imprimirFavicon(); ?>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Subir Imagen Vehículos</span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="<?= volverSegunRol() ?>" class="nav-link">Volver</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="mb-4">Subida masiva de imagen por marca y modelo</h1>

                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>

                <?php if ($errores): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <select name="marca" id="marca" class="form-select" required onchange="actualizarModelos()">
                                <option value="">-- Selecciona Marca --</option>
                                <?php foreach ($marcas as $m): ?>
                                    <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Modelo</label>
                            <select name="modelo" id="modelo" class="form-select" required>
                                <option value="">-- Selecciona Modelo --</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="imagen" class="form-control" accept=".jpg,.jpeg,.png" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="subirImagen" class="btn btn-success mt-3">Subir Imagen</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modelosPorMarca = <?= json_encode($allModelos) ?>;

        function actualizarModelos() {
            const marcaSel = document.getElementById('marca').value;
            const modeloSel = document.getElementById('modelo');
            modeloSel.innerHTML = '<option value="">-- Selecciona Modelo --</option>';

            if (marcaSel && modelosPorMarca[marcaSel]) {
                Object.values(modelosPorMarca[marcaSel]).forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m;
                    modeloSel.appendChild(opt);
                });
            }
        }
    </script>

</body>

</html>