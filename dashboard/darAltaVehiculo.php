<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico']);
require_once '../includes/Vehiculo.php';


$pdo = getPDO();

//manejo de mensajes
$mensaje = '';

if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}

//manejo de errores
$errores = [];
$errores = $_SESSION['errores_alta_vehiculo'] ?? [];

//conservar datos introducidos en inputs
$old = $_SESSION['old_input_vehiculo'] ?? [];
unset($_SESSION['errores_alta_vehiculo'], $_SESSION['old_input_vehiculo']);


$estadoPorDefecto = $pdo->query("SELECT idEstado FROM EstadoVehiculo WHERE nombreEstado = 'SUCIO'")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        //control de redireccion segun boton pulsado
        $accion = $_POST['accion'] ?? 'alta';

        //calcular fecha
        $fechaUltimaRevision = $_POST['fechaUltimaRevision'] ?? null;
        $fechaProximaRevision = $fechaUltimaRevision
            ? date('Y-m-d', strtotime($fechaUltimaRevision . ' +1 year'))
            : null;

        // normalizar y limpiar
        $datos = [
            'matricula' => isset($_POST['matricula']) ? strtoupper(trim($_POST['matricula'])) : '',
            'marca' => isset($_POST['marca']) ? strtoupper(trim($_POST['marca'])) : '',
            'modelo' => isset($_POST['modelo']) ? strtoupper(trim($_POST['modelo'])) : '',
            'anio' => $_POST['anio'] ?? 0,
            'color' => isset($_POST['color']) ? strtoupper(trim($_POST['color'])) : '',
            'numeroPlazas' => $_POST['numeroPlazas'] ?? 0,

            //----------------------------------------
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

        //Crear clase, validar todo y guardar
        $vehiculo = new Vehiculo($datos, $pdo);
        $resultado = $vehiculo->guardarNuevo();



        if (!empty($resultado['errores'])) {
            $_SESSION['errores_alta_vehiculo'] = $resultado['errores'];
            $_SESSION['old_input_vehiculo'] = $_POST;

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        } else {
            $_SESSION['mensaje'] = "Vehículo ha sido dado de alta.";

            //redirige a la parte de venta pasando id de vehiculo recien agregado
            if (isset($_POST['accion']) && $_POST['accion'] === 'vender') {
                header("Location: venderVehiculo.php?idVehiculo=" . $resultado['idVehiculo']);
                exit;
            }

            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['errores_alta_vehiculo'] = [
            'general' => $e->getMessage()
        ];

        $_SESSION['old_input_vehiculo'] = $_POST;

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Consultas para selects
$categorias = $pdo->query("SELECT idCategoria, nombreCategoria FROM Categoria")->fetchAll();
$tiposPropulsion = ['Gasolina', 'Diesel', 'Hibrido', 'Electrico'];
$transmisiones = ['Manual', 'Automatico'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php imprimirFavicon(); ?>
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
                    <li class="nav-item"><a href="<?= volverSegunRol() ?>" class="nav-link">Volver</a></li>
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
                            <input type="text" class="form-control" name="matricula" value="<?= htmlspecialchars($old['matricula'] ?? '') ?>" placeholder="Matrícula" required>
                        </div>

                        <div class="col-md-6">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" name="marca" value="<?= htmlspecialchars($old['marca'] ?? '') ?>" placeholder="Marca" required>
                        </div>

                        <div class="col-md-6">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" class="form-control" name="modelo" value="<?= htmlspecialchars($old['modelo'] ?? '') ?>" placeholder="Modelo" required>
                        </div>

                        <div class="col-md-3">
                            <label for="anio" class="form-label">Año</label>
                            <input type="number" class="form-control" name="anio" value="<?= htmlspecialchars($old['anio'] ?? '') ?>" placeholder="Año" min="1900" max="<?= date('Y') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" value="<?= htmlspecialchars($old['color'] ?? '') ?>" placeholder="Color">
                        </div>

                        <div class="col-md-3">
                            <label for="numeroPlazas" class="form-label">Número de plazas</label>
                            <input type="number" class="form-control" name="numeroPlazas" value="<?= htmlspecialchars($old['numeroPlazas'] ?? '') ?>" placeholder="Número de plazas" min="2" max="9" required>
                        </div>

                        <div class="col-md-3">
                            <label for="tipoPropulsion" class="form-label">Tipo de Propulsión</label>
                            <select class="form-select" name="tipoPropulsion" required>
                                <option value="">-- Tipo de Propulsión --</option>
                                <?php foreach ($tiposPropulsion as $tipo): ?>
                                    <option value="<?= $tipo ?>"
                                        <?= ($old['tipoPropulsion'] ?? '') === $tipo ? 'selected' : '' ?>>
                                        <?= $tipo ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="transmision" class="form-label">Transmisión</label>
                            <select class="form-select" name="transmision" required>
                                <option value="">-- Transmisión --</option>
                                <?php foreach ($transmisiones as $tr): ?>
                                    <option value="<?= $tr ?>"
                                        <?= ($old['transmision'] ?? '') === $tr ? 'selected' : '' ?>>
                                        <?= $tr ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="idCategoria" class="form-label">Categoría</label>
                            <select class="form-select" name="idCategoria" required>
                                <option value="">-- Seleccione categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idCategoria'] ?>"
                                        <?= ($old['idCategoria'] ?? '') == $cat['idCategoria'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombreCategoria']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <input type="hidden" name="idEstado" value="<?= $estadoPorDefecto ?>">

                        <div class="col-md-3">
                            <label for="kmInicial" class="form-label">Kilómetros iniciales</label>
                            <input type="number" class="form-control" name="kmInicial" value="<?= htmlspecialchars($old['kmInicial'] ?? '') ?>" placeholder="Kilómetros iniciales" min="0" required>
                        </div>

                        <div class="col-md-3">
                            <label for="fechaUltimaRevision" class="form-label">Fecha última revisión</label>
                            <input type="date" class="form-control" name="fechaUltimaRevision" value="<?= htmlspecialchars($old['fechaUltimaRevision'] ?? '') ?>" placeholder="Fecha última revisión">
                        </div>

                        <div class="col-md-3">
                            <label for="precioAdquisicion" class="form-label">Precio de adquisición (€)</label>
                            <input type="number" class="form-control" step="0.01" name="precioAdquisicion" value="<?= htmlspecialchars($old['precioAdquisicion'] ?? '') ?>" placeholder="Precio adquisición">
                        </div>

                        <div class="col-md-3">
                            <label for="fechaAdquisicion" class="form-label">Fecha de adquisición</label>
                            <input type="date" class="form-control" name="fechaAdquisicion" value="<?= htmlspecialchars($old['fechaAdquisicion'] ?? '') ?>" placeholder="Fecha adquisición">
                        </div>

                        <div class="col-12">
                            <label for="notasInternas" class="form-label">Notas internas</label>
                            <textarea class="form-control" name="notasInternas" placeholder="Notas internas"><?= htmlspecialchars($old['notasInternas'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 d-flex gap-4">
                            <button type="submit" name="accion" value="alta" class="btn btn-primary mt-3">
                                Solamente Alta
                            </button>

                            <button type="submit" name="accion" value="vender" class="btn btn-primary mt-3">
                                Alta y Vender
                            </button>
                        </div>


                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>