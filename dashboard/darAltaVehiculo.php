<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico']);
require_once '../includes/Vehiculo.php';


$pdo = getPDO();
$mensaje = '';
$errores = [];


$estadoPorDefecto = $pdo->query("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = 'SUCIO'")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $fechaUltimaRevision = $_POST['fechaUltimaRevision'] ?? null;
        $fechaProximaRevision = $fechaUltimaRevision
            ? date('Y-m-d', strtotime($fechaUltimaRevision . ' +1 year'))
            : null;

        // Pasar a mayus y trimear
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
            'idEstado' => $estadoPorDefecto,
            'disponibilidad' => false,
            'kmInicial' => $_POST['kmInicial'] ?? 0,
            'kmActual' => $_POST['kmInicial'] ?? 0,
            'fechaUltimaRevision' => $fechaUltimaRevision,
            'fechaProximaRevision' => $fechaProximaRevision,
            'precioAdquisicion' => $_POST['precioAdquisicion'] ?? 0,
            'fechaAdquisicion' => $_POST['fechaAdquisicion'] ?? null,
            'contadorReservas' => 0,
            'notasInternas' => $_POST['notasInternas'] ?? '',
            'manipuladoPor' => $_SESSION['usuario']['dni'] ?? null
        ];


        if ($datos['tipoPropulsion'] === 'Eléctrico' && $datos['transmision'] !== 'Automático') {
            throw new Exception("Los vehículos eléctricos solo pueden ser Automáticos.");
        }


        if ($datos['numeroPlazas'] < 2 || $datos['numeroPlazas'] > 9) {
            throw new Exception("Número de plazas debe estar entre 2 y 9.");
        }

        $vehiculo = new Vehiculo($datos, $pdo);
        $resultado = $vehiculo->guardar();

        if (!empty($resultado['errores'])) {
            $errores = $resultado['errores'];
        } else {
            $mensaje = "Vehículo dado de alta correctamente.";
        }
    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}

// Consultas para selects
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
    <title>Dar de Alta Vehículo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">


    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Alta nuevo vehículo</span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Volver</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">

        <div class="card mb-4">
            <div class="card-body">
                <h1 class="mb-4">Dar de Alta Vehículo</h1>

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
                            <input type="text" class="form-control" name="matricula" placeholder="Matrícula" required>
                        </div>

                        <div class="col-md-6">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" name="marca" placeholder="Marca" required>
                        </div>

                        <div class="col-md-6">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" name="modelo" placeholder="Modelo" required>
                        </div>

                        <div class="col-md-3">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number" class="form-control" name="anio" placeholder="Año" min="1900" max="<?= date('Y') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" placeholder="Color" required>
                        </div>

                        <div class="col-md-3">
                            <label for="numeroPlazas" class="form-label">Número de plazas</label>
                            <input type="number" class="form-control" name="numeroPlazas" placeholder="Número de plazas" min="2" max="9" required>
                        </div>

                        <div class="col-md-3">
                            <label for="tipoPropulsion" class="form-label">Tipo de Propulsión</label>
                            <select class="form-select" name="tipoPropulsion" required>
                                <option value="">-- Tipo de Propulsión --</option>
                                <?php foreach ($tiposPropulsion as $tipo): ?>
                                    <option value="<?= $tipo ?>"><?= $tipo ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="transmision" class="form-label">Transmisión</label>
                            <select class="form-select" name="transmision" required>
                                <option value="">-- Transmisión --</option>
                                <?php foreach ($transmisiones as $tr): ?>
                                    <option value="<?= $tr ?>"><?= $tr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="idCategoria" class="form-label">Categoría</label>
                            <select class="form-select" name="idCategoria" required>
                                <option value="">-- Seleccione categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nombreCategoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="idEstado" value="<?= $estadoPorDefecto ?>">

                        <div class="col-md-3">
                            <label for="kmInicial" class="form-label">Kilómetros iniciales</label>
                            <input type="number" class="form-control" name="kmInicial" placeholder="Kilómetros iniciales" min="0" required>
                        </div>

                        <div class="col-md-3">
                            <label for="fechaUltimaRevision" class="form-label">Fecha última revisión</label>
                            <input type="date" class="form-control" name="fechaUltimaRevision" placeholder="Fecha última revisión">
                        </div>

                        <div class="col-md-3">
                            <label for="precioAdquisicion" class="form-label">Precio de adquisición (€)</label>
                            <input type="number" class="form-control" step="0.01" name="precioAdquisicion" placeholder="Precio adquisición">
                        </div>

                        <div class="col-md-3">
                            <label for="fechaAdquisicion" class="form-label">Fecha de adquisición</label>
                            <input type="date" class="form-control" name="fechaAdquisicion" placeholder="Fecha adquisición">
                        </div>

                        <div class="col-12">
                            <label for="notasInternas" class="form-label">Notas internas</label>
                            <textarea class="form-control" name="notasInternas" placeholder="Notas internas"></textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary mt-3">Dar de Alta Vehículo</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>