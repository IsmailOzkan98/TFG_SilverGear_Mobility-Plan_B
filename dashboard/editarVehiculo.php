<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico']);
require_once '../includes/Vehiculo.php';

$pdo = getPDO();
$mensaje = '';
$errores = [];

// Obtener ID del vehículo
$idVehiculo = $_GET['idVehiculo'] ?? null;
if (!$idVehiculo) die("Vehículo no especificado.");

// Cargar datos desde DB
$stmt = $pdo->prepare("SELECT * FROM Vehiculo WHERE idVehiculo = :idVehiculo");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$datosDB = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$datosDB) die("Vehículo no encontrado.");

// Crear objeto Vehiculo
$vehiculo = new Vehiculo($datosDB, $pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $estadoAnterior = $vehiculo->idEstado;

        // Actualizar datos 
        $vehiculo->matricula = strtoupper(trim($_POST['matricula']));
        $vehiculo->marca = strtoupper(trim($_POST['marca']));
        $vehiculo->modelo = strtoupper(trim($_POST['modelo']));
        $vehiculo->anio = $_POST['anio'];
        $vehiculo->color = strtoupper(trim($_POST['color']));
        $vehiculo->numeroPlazas = $_POST['numeroPlazas'];
        $vehiculo->tipoPropulsion = $_POST['tipoPropulsion'];
        $vehiculo->transmision = $_POST['transmision'];
        $vehiculo->idCategoria = $_POST['idCategoria'];
        $vehiculo->idEstado = $_POST['idEstado'];
        $vehiculo->kmActual = $_POST['kmActual'];
        $vehiculo->fechaUltimaRevision = $_POST['fechaUltimaRevision'] ?? null;
        $vehiculo->fechaProximaRevision = $vehiculo->fechaUltimaRevision
            ? date('Y-m-d', strtotime($vehiculo->fechaUltimaRevision . ' +1 year'))
            : null;
        $vehiculo->precioAdquisicion = $_POST['precioAdquisicion'];
        $vehiculo->fechaAdquisicion = $_POST['fechaAdquisicion'];
        $vehiculo->notasInternas = $_POST['notasInternas'];
        $vehiculo->manipuladoPor = $_SESSION['usuario']['dni'] ?? null;

        // Validaciones
        if ($vehiculo->tipoPropulsion === 'Eléctrico' && $vehiculo->transmision !== 'Automático') {
            throw new Exception("Los vehículos eléctricos solo pueden ser Automáticos.");
        }

        if ($vehiculo->numeroPlazas < 2 || $vehiculo->numeroPlazas > 9) {
            throw new Exception("Número de plazas debe estar entre 2 y 9.");
        }

        // Cambios de estado
        if ($estadoAnterior != $vehiculo->idEstado) {
            cambiarEstadoVehiculo($pdo, $vehiculo, $vehiculo->idEstado, $vehiculo->manipuladoPor);
        } else {
            actualizarDisponibilidadVehiculo($vehiculo);
        }

        $vehiculo->guardar();

        $accion = $estadoAnterior != $vehiculo->idEstado
            ? "Edicion de vehiculo y cambio de su estado."
            : "Edicion de vehículo";
        registrarHistorialVehiculo(
            $pdo,
            $vehiculo->matricula,
            $vehiculo->manipuladoPor,
            $accion,
            "Se actualizaron datos generales"
        );

        // Actualizar
        $datosDB = [
            'matricula' => $vehiculo->matricula,
            'marca' => $vehiculo->marca,
            'modelo' => $vehiculo->modelo,
            'anio' => $vehiculo->anio,
            'color' => $vehiculo->color,
            'numeroPlazas' => $vehiculo->numeroPlazas,
            'tipoPropulsion' => $vehiculo->tipoPropulsion,
            'transmision' => $vehiculo->transmision,
            'idCategoria' => $vehiculo->idCategoria,
            'idEstado' => $vehiculo->idEstado,
            'kmInicial' => $vehiculo->kmInicial,
            'kmActual' => $vehiculo->kmActual,
            'fechaUltimaRevision' => $vehiculo->fechaUltimaRevision,
            'fechaProximaRevision' => $vehiculo->fechaProximaRevision,
            'precioAdquisicion' => $vehiculo->precioAdquisicion,
            'fechaAdquisicion' => $vehiculo->fechaAdquisicion,
            'notasInternas' => $vehiculo->notasInternas,
            'disponibilidad' => $vehiculo->disponibilidad
        ];

        $mensaje = "Vehículo actualizado correctamente.";

    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}


// Datos para selects
$rol = getUserRole();
$estadosPermitidos = [];

if ($rol === 'admin') {
    $estadosPermitidos = ['LIMPIO', 'SUCIO', 'IMPRO'];
} elseif ($rol === 'mecanico') {
    $estadosPermitidos = ['SUCIO', 'IMPRO'];
}


$placeholders = implode(',', array_fill(0, count($estadosPermitidos), '?'));
$estados = $pdo->prepare("
    SELECT idEstado, nombreEstado 
    FROM EstadoVehiculo 
    WHERE UPPER(TRIM(nombreEstado)) IN ($placeholders)
");
$estados->execute(array_map('strtoupper', $estadosPermitidos));
$estados = $estados->fetchAll(PDO::FETCH_ASSOC);


$categorias = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria")->fetchAll();
$tiposPropulsion = ['Gasolina', 'Diesel', 'Híbrido', 'Eléctrico'];
$transmisiones = ['Manual', 'Automático'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vehículo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Editar vehículo</span>
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
                <h1 class="mb-4">Editar Vehículo</h1>

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

                <form method="post">
                    <div class="row g-3">
                        
                        <div class="col-md-6">
                            <label class="form-label">Matrícula</label>
                            <input type="text" class="form-control" name="matricula" value="<?= htmlspecialchars($datosDB['matricula']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Marca</label>
                            <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($datosDB['marca']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modelo</label>
                            <input type="text" class="form-control" name="modelo" value="<?= htmlspecialchars($datosDB['modelo']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Año</label>
                            <input type="number" class="form-control" name="anio" value="<?= htmlspecialchars($datosDB['anio']) ?>" min="1900" max="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" value="<?= htmlspecialchars($datosDB['color']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Número de plazas</label>
                            <input type="number" class="form-control" name="numeroPlazas" value="<?= htmlspecialchars($datosDB['numeroPlazas']) ?>" min="2" max="9" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de Propulsión</label>
                            <select class="form-select" name="tipoPropulsion" required>
                                <option value="">-- Tipo de Propulsión --</option>
                                <?php foreach ($tiposPropulsion as $tipo): ?>
                                    <option value="<?= $tipo ?>" <?= $datosDB['tipoPropulsion'] === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Transmisión</label>
                            <select class="form-select" name="transmision" required>
                                <option value="">-- Transmisión --</option>
                                <?php foreach ($transmisiones as $tr): ?>
                                    <option value="<?= $tr ?>" <?= $datosDB['transmision'] === $tr ? 'selected' : '' ?>><?= $tr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="idCategoria" required>
                                <option value="">-- Seleccione categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idCategoria'] ?>" <?= $datosDB['idCategoria'] == $cat['idCategoria'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombreCategoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="idEstado" required>
                                <option value="" <?= empty($datosDB['idEstado']) ? 'selected' : '' ?>>-- Seleccione estado --</option>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['idEstado'] ?>"
                                        <?= (string)$datosDB['idEstado'] === (string)$estado['idEstado'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($estado['nombreEstado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Disponibilidad</label>
                            <input type="checkbox" class="form-check-input" <?= $datosDB['disponibilidad'] ? 'checked' : '' ?> disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kilómetros iniciales</label>
                            <input type="number" class="form-control" value="<?= htmlspecialchars($datosDB['kmInicial']) ?>" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Kilómetros actuales</label>
                            <input type="number" class="form-control" name="kmActual" value="<?= htmlspecialchars($datosDB['kmActual']) ?>" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha última revisión</label>
                            <input type="date" class="form-control" name="fechaUltimaRevision" value="<?= htmlspecialchars($datosDB['fechaUltimaRevision']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Precio de adquisición (€)</label>
                            <input type="number" class="form-control" step="0.01" name="precioAdquisicion" value="<?= htmlspecialchars($datosDB['precioAdquisicion']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha de adquisición</label>
                            <input type="date" class="form-control" name="fechaAdquisicion" value="<?= htmlspecialchars($datosDB['fechaAdquisicion']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notas internas</label>
                            <textarea class="form-control" name="notasInternas"><?= htmlspecialchars($datosDB['notasInternas']) ?></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary mt-3">Actualizar Vehículo</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>