<?php
require_once '../includes/common.php';
require_once '../includes/security.php';

requireRole(['admin', 'ventas']);

$pdo = getPDO();

$mensaje = '';
$errores = [];
$erroresImagenes = false;

// Obtener ID
$idVehiculo = $_GET['idVehiculo'] ?? null;
if (!$idVehiculo) {
    die("Artículo no especificado.");
}

//Obtener Imagenes
$stmtImgs = $pdo->prepare("SELECT * FROM Vehiculo_Imagenes WHERE idVehiculo=:idVehiculo");
$stmtImgs->execute([':idVehiculo' => $idVehiculo]);
$imagenes = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

//cargar datos 
$stmt = $pdo->prepare("SELECT * FROM Vehiculo WHERE idVehiculo = :idVehiculo");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$vehiculo) {
    die("Artículo no encontrado.");
}

$idCategoria = $vehiculo['idCategoria'];
$categoria = getNombreCategoria($idCategoria);



// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $precioVenta = $_POST['precioVenta'] ?? null;
        $notasInternas = $_POST['notasInternas'] ?? null;

        if ($precioVenta === null || $precioVenta < 0) {
            throw new Exception("El precio no es válido.");
        }

        $imagenes = $_FILES['imagenes'] ?? null;

        if (!empty($imagenes['name'][0])) {

            for ($i = 0; $i < count($imagenes['name']); $i++) {

                $name = $imagenes['name'][$i];
                $size = $imagenes['size'][$i];
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $errores[] = "Archivo '$name' no válido.";
                    $erroresImagenes = true;
                }

                if ($size > 20 * 1024 * 1024) {
                    $errores[] = "Archivo '$name' demasiado grande.";
                    $erroresImagenes = true;
                }
            }
        }

        if (!empty($errores)) {
            throw new Exception("VALIDACION");
        }

        $stmt = $pdo->prepare("
            UPDATE Vehiculo
            SET precioVenta = :precioVenta,
                notasInternas = :notasInternas
            WHERE idVehiculo = :idVehiculo
        ");

        $stmt->execute([
            ':precioVenta' => $precioVenta,
            ':notasInternas' => $notasInternas,
            ':idVehiculo' => $idVehiculo
        ]);

        $vehiculo['precioVenta'] = $precioVenta;
        $vehiculo['notasInternas'] = $notasInternas;

        if (!empty($imagenes['name'][0])) {

            $matriculaRuta = preg_replace('/[^A-Z0-9\-]/i', '_', $vehiculo['matricula']);
            $carpetaDestino = '../images/Ventas/' . $matriculaRuta . '/';

            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0755, true);
            }

            for ($i = 0; $i < count($imagenes['name']); $i++) {

                $tmp = $imagenes['tmp_name'][$i];
                $name = $imagenes['name'][$i];

                $ruta = $carpetaDestino . time() . '_' . $name;

                move_uploaded_file($tmp, $ruta);

                $stmtImg = $pdo->prepare("
            INSERT INTO Vehiculo_Imagenes (idVehiculo, rutaImagen)
            VALUES (:idVehiculo, :rutaImagen)
        ");

                $stmtImg->execute([
                    ':idVehiculo' => $idVehiculo,
                    ':rutaImagen' => $ruta
                ]);
            }
        }

        $mensaje = "Artículo actualizado correctamente.";
    } catch (Exception $e) {
        $errores[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Artículo de Compra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php imprimirFavicon(); ?>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Editar articulo · <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="../pages/tiendaComprar.php" class="nav-link">Volver a la tienda</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-body">

                <?php if ($mensaje): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
                <?php endif; ?>

                <?php if ($errores): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errores as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Matrícula</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['matricula']) ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Marca</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['marca']) ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Modelo</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['modelo']) ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Plazas</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['numeroPlazas'] ?? '') ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Categoría</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($categoria ?? '') ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Transmisión</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['transmision'] ?? '') ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tipo Propulsion</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['tipoPropulsion'] ?? '') ?>" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Color</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($vehiculo['color'] ?? '') ?>" readonly disabled>
                    </div>

                </div>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Precio de venta (€)</label>
                        <input type="number"
                            step="0.01"
                            min="0"
                            name="precioVenta"
                            class="form-control"
                            value="<?= htmlspecialchars($vehiculo['precioVenta']) ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="notasInternas"
                            class="form-control"
                            rows="4"><?= htmlspecialchars($vehiculo['notasInternas']) ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Subir imagenes del vehiculo (JPG, PNG)</label>
                        <input type="file" class="form-control" name="imagenes[]" multiple>
                    </div>

                    <?php if ($imagenes): ?>
                        <div class="col-12 mt-3">
                            <label class="form-label">Imágenes actuales:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($imagenes as $img): ?>
                                    <?php if (!empty($img['rutaImagen'])): ?>
                                        <img src="<?= htmlspecialchars($img['rutaImagen']) ?>" alt="Imagen" style="height:100px;">
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            Guardar cambios
                        </button>

                        <a href="../pages/tiendaComprar.php"
                            class="btn btn-secondary ms-2">
                            Volver
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>

</body>

</html>