<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico', 'ventas']);
require_once '../includes/Vehiculo.php';

$pdo = getPDO();

//manejo de mensajes
$errores = [];
$imagenesErrores = false;
$erroresImagenesTexto = [];

$mensajeExito = $_SESSION['mensaje_exito'] ?? '';
unset($_SESSION['mensaje_exito']);

$mensajeError = $_SESSION['mensaje_error'] ?? '';
unset($_SESSION['mensaje_error']);

$erroresForm = $_SESSION['errores_alta_vehiculo'] ?? [];
unset($_SESSION['errores_alta_vehiculo']);

//datos de input
$old = $_SESSION['old_input_vehiculo'] ?? [];
unset($_SESSION['old_input_vehiculo']);

//Obtener vh
$idVehiculo = $_GET['idVehiculo'] ?? null;

if (!$idVehiculo) die("Vehículo no especificado.");

//cargar datos 
$stmt = $pdo->prepare("SELECT * FROM Vehiculo WHERE idVehiculo = :idVehiculo");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$datosDB = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$datosDB) die("Vehículo no encontrado.");

$vehiculo = new Vehiculo($datosDB, $pdo);

//obtener matricula
$matricula = $datosDB['matricula'];
$matriculaRuta = preg_replace('/[^A-Z0-9\-]/i', '_', $matricula);



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehiculo->notasInternas = $_POST['notasInternas'] ?? $vehiculo->notasInternas;

    //precio y su validacion
    $vehiculo->precioVenta = $_POST['precioVenta'] ?? $vehiculo->precioVenta;
    $validacionPrecio = validarNoNegativo($vehiculo->precioVenta, 'Precio venta');
    if ($validacionPrecio !== true) {
        $errores['precioVenta'] = $validacionPrecio;
    }

    if (!empty($errores)) {
        $_SESSION['errores_alta_vehiculo'] = $errores;
        $_SESSION['old_input_vehiculo'] = $_POST;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    $stmt = $pdo->prepare("
            UPDATE Vehiculo 
            SET precioVenta = :precioVenta,
                notasInternas = :notasInternas
            WHERE idVehiculo = :idVehiculo
        ");

    $stmt->execute([
        ':precioVenta' => $vehiculo->precioVenta,
        ':notasInternas' => $vehiculo->notasInternas,
        ':idVehiculo' => $idVehiculo
    ]);

    $vehiculo->manipuladoPor = $_SESSION['usuario']['dni'] ?? null;


    $stmtEstado = $pdo->prepare("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado='VENTAS'");
    $stmtEstado->execute();
    $idVentas = $stmtEstado->fetchColumn();
    if (!$idVentas) {
        die("ERROR: Estado VENTAS no encontrado");
    }


    cambiarEstadoVehiculo($pdo, $vehiculo, $idVentas, $vehiculo->manipuladoPor, $vehiculo->notasInternas);

    //subida de las imagenes
    if (!empty($_FILES['imagenes']['name'][0])) {

        //donde se guarda y si no lo crea
        $carpetaDestino = '../images/Ventas/' . $matriculaRuta . '/';
        if (!is_dir($carpetaDestino)) mkdir($carpetaDestino, 0755, true);

        $archivos = $_FILES['imagenes'];

        for ($i = 0; $i < count($archivos['name']); $i++) {

            $nombreArchivo = basename($archivos['name'][$i]);

            //obtener datos basicos
            $tmp = $archivos['tmp_name'][$i];
            $size = $archivos['size'][$i];

            //obtener extension y comprobarla
            $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $imagenesErrores = true;
                $erroresImagenesTexto[] = "Archivo '$nombreArchivo' no es JPG o PNG.";
                continue;
            }

            //verificar peso 
            if ($size > 20 * 1024 * 1024) {
                $imagenesErrores = true;
                $erroresImagenesTexto[] = "Archivo '$nombreArchivo' supera los 20MB.";
                continue;
            }

            $rutaDestino = $carpetaDestino . time() . '_' . $nombreArchivo; //genera nombre unico usando timestamp


            //moverlo a server y asociar ruta con el vehiculo
            if (move_uploaded_file($tmp, $rutaDestino)) {
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

    $_SESSION['mensaje_exito'] = "Vehículo puesto a la venta correctamente.";

    if ($imagenesErrores) {
        $_SESSION['mensaje_error'] = implode("<br>", $erroresImagenesTexto);
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
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
    <?php imprimirFavicon(); ?>
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

                <?php if ($mensajeExito): ?>
                    <div class="alert alert-success">
                        <?= $mensajeExito ?>
                    </div>
                <?php endif; ?>

                <?php if ($mensajeError): ?>
                    <div class="alert alert-warning">
                        <?= $mensajeError ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erroresForm)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($erroresForm as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Matrícula</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($datosDB['matricula']) ?>" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($datosDB['marca']) ?>" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($datosDB['modelo']) ?>" readonly disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado actual</label>
                            <input type="text" class="form-control" value="<?= Vehiculo::obtenerNombreEstado($vehiculo->idEstado) ?>" readonly disabled>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Precio de venta (€)</label>
                            <input type="number" step="0.01" class="form-control" name="precioVenta" value="<?= htmlspecialchars($old['precioVenta'] ?? $vehiculo->precioVenta ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Precio de adquisicion (€)</label>
                            <input type="number" class="form-control" value="<?= htmlspecialchars($vehiculo->precioAdquisicion) ?>" readonly disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notas internas</label>
                            <textarea class="form-control" name="notasInternas"><?= htmlspecialchars($old['notasInternas'] ?? $vehiculo->notasInternas) ?></textarea>
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
                                        <img src="<?= htmlspecialchars($img['rutaImagen']) ?>" alt="Imagen" style="height:100px;">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary mt-3">Poner a la venta</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>