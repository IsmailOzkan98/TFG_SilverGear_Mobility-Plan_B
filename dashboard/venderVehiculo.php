<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico', 'ventas']);
require_once '../includes/Vehiculo.php';

$pdo = getPDO();
$mensaje = '';
$errores = [];

// Obtener vh
$idVehiculo = $_GET['idVehiculo'] ?? null;
if (!$idVehiculo) die("Vehículo no especificado.");

// Cargar datos 
$stmt = $pdo->prepare("SELECT * FROM Vehiculo WHERE idVehiculo = :idVehiculo");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$datosDB = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$datosDB) die("Vehículo no encontrado.");

$vehiculo = new Vehiculo($datosDB, $pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $vehiculo->notasInternas = $_POST['notasInternas'] ?? $vehiculo->notasInternas;
        $vehiculo->manipuladoPor = $_SESSION['usuario']['dni'] ?? null;

        
        $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado='VENTAS'");
        $stmtEstado->execute();
        $idVentas = $stmtEstado->fetchColumn();
        if (!$idVentas) throw new Exception("ERROR: Estado VENTAS no encontrado");

        
        cambiarEstadoVehiculo($pdo, $vehiculo, $idVentas, $vehiculo->manipuladoPor, $vehiculo->notasInternas);

        // Subida de imagenes
        if (!empty($_FILES['imagenes']['name'][0])) {
            $carpetaDestino = '../images/Ventas/' . $idVehiculo . '/';
            if (!is_dir($carpetaDestino)) mkdir($carpetaDestino, 0755, true);

            $archivos = $_FILES['imagenes'];
            for ($i = 0; $i < count($archivos['name']); $i++) {
                $nombreArchivo = basename($archivos['name'][$i]);
                $rutaDestino = $carpetaDestino . time() . '_' . $nombreArchivo;

                if (move_uploaded_file($archivos['tmp_name'][$i], $rutaDestino)) {
                    $stmtImg = $pdo->prepare("
                        INSERT INTO Vehiculo_Imagenes (idVehiculo, rutaImagen) 
                        VALUES (:idVehiculo, :rutaImagen)
                    ");
                    $stmtImg->execute([
                        ':idVehiculo' => $idVehiculo,
                        ':rutaImagen' => $rutaDestino
                    ]);
                }
            }
        }

        $mensaje = "Vehículo puesto a la venta e imágenes subidas correctamente!";

    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}

//imagenes actuales
$stmtImgs = $pdo->prepare("SELECT * FROM Vehiculo_Imagenes WHERE idVehiculo=:idVehiculo");
$stmtImgs->execute([':idVehiculo' => $idVehiculo]);
$imagenes = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poner a la venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">SilverGear Mobility - Poner a la venta</span>
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
            <h1 class="mb-4">Poner Vehículo a la Venta</h1>

            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <?php if ($errores): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $campo => $error): ?>
                            <li><?= htmlspecialchars("$campo: $error") ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Matrícula</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($datosDB['matricula']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Marca</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($datosDB['marca']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Modelo</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($datosDB['modelo']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado actual</label>
                        <input type="text" class="form-control" value="<?= Vehiculo::obtenerNombreEstado($vehiculo->idEstado) ?>" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notas internas</label>
                        <textarea class="form-control" name="notasInternas"><?= htmlspecialchars($vehiculo->notasInternas) ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Subir imágenes del vehículo</label>
                        <input type="file" class="form-control" name="imagenes[]" multiple>
                    </div>

                    <?php if ($imagenes): ?>
                        <div class="col-12 mt-3">
                            <label class="form-label">Imágenes actuales:</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($imagenes as $img): ?>
                                    <img src="<?= htmlspecialchars($img['rutaImagen']) ?>" alt="Imagen" style="height:100px;">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-12">
                        <button type="submit" class="btn btn-success mt-3">Poner a la venta</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
