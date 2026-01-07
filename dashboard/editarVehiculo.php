<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico']);
require_once '../includes/Vehiculo.php';

$pdo = getPDO();
$mensaje = '';
$errores = [];

// Obtener ID 
$idVehiculo = $_GET['idVehiculo'] ?? null;
if (!$idVehiculo) {
    die("Vehículo no especificado.");
}

// Cargar datos 
$stmt = $pdo->prepare("SELECT * FROM Vehiculo WHERE idVehiculo = :idVehiculo");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$vehiculoDB = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$vehiculoDB) {
    die("Vehículo no encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fechaUltimaRevision = $_POST['fechaUltimaRevision'] ?? null;
        $fechaProximaRevision = $fechaUltimaRevision
            ? date('Y-m-d', strtotime($fechaUltimaRevision . ' +1 year'))
            : null;

        $datos = [
            'matricula' => isset($_POST['matricula']) ? strtoupper(trim($_POST['matricula'])) : '',
            'marca' => isset($_POST['marca']) ? strtoupper(trim($_POST['marca'])) : '',
            'modelo' => isset($_POST['modelo']) ? strtoupper(trim($_POST['modelo'])) : '',
            'anio' => $_POST['anio'] ?? 0,
            'color' => isset($_POST['color']) ? strtoupper(trim($_POST['color'])) : '',
            'numeroPlazas' => $_POST['numeroPlazas'] ?? 0,
            'tipoPropulsion' => $_POST['tipoPropulsion'] ?? '',
            'transmision' => $_POST['transmision'] ?? '',
            'idCategoria' => $_POST['idCategoria'] ?? null,
            'idEstado' => $_POST['idEstado'] ?? $vehiculoDB['idEstado'],
            'disponibilidad' => isset($_POST['disponibilidad']) ? ($_POST['disponibilidad'] ? 1 : 0) : $vehiculoDB['disponibilidad'],
            'kmInicial' => $vehiculoDB['kmInicial'],
            'kmActual' => $_POST['kmActual'] ?? $vehiculoDB['kmActual'],
            'fechaUltimaRevision' => $fechaUltimaRevision,
            'fechaProximaRevision' => $fechaProximaRevision,
            'precioAdquisicion' => $_POST['precioAdquisicion'] ?? $vehiculoDB['precioAdquisicion'],
            'fechaAdquisicion' => $_POST['fechaAdquisicion'] ?? $vehiculoDB['fechaAdquisicion'],
            'notasInternas' => $_POST['notasInternas'] ?? $vehiculoDB['notasInternas'],
            'manipuladoPor' => $_SESSION['usuario']['dni'] ?? null
        ];

        if ($datos['tipoPropulsion'] === 'Eléctrico' && $datos['transmision'] !== 'Automático') {
            throw new Exception("Los vehículos eléctricos solo pueden ser Automáticos.");
        }

        if ($datos['numeroPlazas'] < 2 || $datos['numeroPlazas'] > 9) {
            throw new Exception("Número de plazas debe estar entre 2 y 9.");
        }

        // Preparar UPDATE
        $updateParams = [
            ':matricula' => $datos['matricula'],
            ':marca' => $datos['marca'],
            ':modelo' => $datos['modelo'],
            ':anio' => $datos['anio'],
            ':color' => $datos['color'],
            ':numeroPlazas' => $datos['numeroPlazas'],
            ':tipoPropulsion' => $datos['tipoPropulsion'],
            ':transmision' => $datos['transmision'],
            ':idCategoria' => $datos['idCategoria'],
            ':idEstado' => $datos['idEstado'],
            ':disponibilidad' => $datos['disponibilidad'],
            ':kmInicial' => $datos['kmInicial'],
            ':kmActual' => $datos['kmActual'],
            ':fechaUltimaRevision' => $datos['fechaUltimaRevision'],
            ':fechaProximaRevision' => $datos['fechaProximaRevision'],
            ':precioAdquisicion' => $datos['precioAdquisicion'],
            ':fechaAdquisicion' => $datos['fechaAdquisicion'],
            ':notasInternas' => $datos['notasInternas'],
            ':manipuladoPor' => $datos['manipuladoPor'],
            ':idVehiculo' => $idVehiculo
        ];

        $stmtUpdate = $pdo->prepare("
            UPDATE Vehiculo SET 
                matricula=:matricula, marca=:marca, modelo=:modelo, anio=:anio, color=:color,
                numeroPlazas=:numeroPlazas, tipoPropulsion=:tipoPropulsion, transmision=:transmision,
                idCategoria=:idCategoria, idEstado=:idEstado, disponibilidad=:disponibilidad,
                kmInicial=:kmInicial, kmActual=:kmActual, fechaUltimaRevision=:fechaUltimaRevision,
                fechaProximaRevision=:fechaProximaRevision, precioAdquisicion=:precioAdquisicion,
                fechaAdquisicion=:fechaAdquisicion, notasInternas=:notasInternas, manipuladoPor=:manipuladoPor
            WHERE idVehiculo=:idVehiculo
        ");

        $stmtUpdate->execute($updateParams);
        $mensaje = "Vehículo actualizado correctamente.";

        // cargar datos 
        $vehiculoDB = array_merge($vehiculoDB, $datos);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062 && str_contains($e->getMessage(), 'matricula')) {
            $errores['matricula'] = 'Matrícula ya registrada';
        } else {
            $errores['general'] = $e->getMessage();
        }
    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}


$categorias = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria")->fetchAll();
$estados = $pdo->query("SELECT idEstado, nombreEstado FROM EstadoVehiculo")->fetchAll();
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
                            <label for="matricula" class="form-label">Matrícula</label>
                            <input type="text" class="form-control" name="matricula" value="<?= htmlspecialchars($vehiculoDB['matricula']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($vehiculoDB['marca']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" name="modelo" value="<?= htmlspecialchars($vehiculoDB['modelo']) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number" class="form-control" name="anio" value="<?= htmlspecialchars($vehiculoDB['anio']) ?>" min="1900" max="<?= date('Y') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" value="<?= htmlspecialchars($vehiculoDB['color']) ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="numeroPlazas" class="form-label">Número de plazas</label>
                            <input type="number" class="form-control" name="numeroPlazas" value="<?= htmlspecialchars($vehiculoDB['numeroPlazas']) ?>" min="2" max="9" required>
                        </div>

                        <div class="col-md-3">
                            <label for="tipoPropulsion" class="form-label">Tipo de Propulsión</label>
                            <select class="form-select" name="tipoPropulsion" required>
                                <option value="">-- Tipo de Propulsión --</option>
                                <?php foreach ($tiposPropulsion as $tipo): ?>
                                    <option value="<?= $tipo ?>" <?= $vehiculoDB['tipoPropulsion'] === $tipo ? 'selected' : '' ?>><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="transmision" class="form-label">Transmisión</label>
                            <select class="form-select" name="transmision" required>
                                <option value="">-- Transmisión --</option>
                                <?php foreach ($transmisiones as $tr): ?>
                                    <option value="<?= $tr ?>" <?= $vehiculoDB['transmision'] === $tr ? 'selected' : '' ?>><?= $tr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="idCategoria" class="form-label">Categoría</label>
                            <select class="form-select" name="idCategoria" required>
                                <option value="">-- Seleccione categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idCategoria'] ?>" <?= $vehiculoDB['idCategoria'] == $cat['idCategoria'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombreCategoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="idEstado" class="form-label">Estado</label>
                            <select class="form-select" name="idEstado" required>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= $estado['idEstado'] ?>" <?= $vehiculoDB['idEstado'] == $estado['idEstado'] ? 'selected' : '' ?>><?= htmlspecialchars($estado['nombreEstado']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="kmInicial" class="form-label">Kilómetros iniciales</label>
                            <input type="number" class="form-control" name="kmInicial" value="<?= htmlspecialchars($vehiculoDB['kmInicial']) ?>" readonly>
                        </div>

                        <div class="col-md-3">
                            <label for="kmActual" class="form-label">Kilómetros actuales</label>
                            <input type="number" class="form-control" name="kmActual" value="<?= htmlspecialchars($vehiculoDB['kmActual']) ?>" min="0" required>
                        </div>

                        <div class="col-md-3">
                            <label for="fechaUltimaRevision" class="form-label">Fecha última revisión</label>
                            <input type="date" class="form-control" name="fechaUltimaRevision" value="<?= htmlspecialchars($vehiculoDB['fechaUltimaRevision']) ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="precioAdquisicion" class="form-label">Precio de adquisición (€)</label>
                            <input type="number" class="form-control" step="0.01" name="precioAdquisicion" value="<?= htmlspecialchars($vehiculoDB['precioAdquisicion']) ?>">
                        </div>

                        <div class="col-md-3">
                            <label for="fechaAdquisicion" class="form-label">Fecha de adquisición</label>
                            <input type="date" class="form-control" name="fechaAdquisicion" value="<?= htmlspecialchars($vehiculoDB['fechaAdquisicion']) ?>">
                        </div>

                        <div class="col-12">
                            <label for="notasInternas" class="form-label">Notas internas</label>
                            <textarea class="form-control" name="notasInternas"><?= htmlspecialchars($vehiculoDB['notasInternas']) ?></textarea>
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