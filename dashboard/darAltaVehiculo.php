<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin','mecanico']);
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
$tiposPropulsion = ['Gasolina','Diesel','Híbrido','Eléctrico'];
$transmisiones = ['Manual','Automático'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dar de Alta Vehículo</title>
    <link rel="stylesheet" href="../css/workerspace.css">
</head>
<body>
    <div class="navbar">
        <div>SilverGear Mobility - Panel Mecánico</div>
        <div>
            <a href="dashboard.php">Volver</a>
            <a href="../includes/logout.php">Cerrar sesion</a>
        </div>
    </div>

    <div class="container">
        <div class="card">
            <h1>Dar de Alta Vehículo</h1>

            <?php if($mensaje): ?>
                <p class="highlight"><?= htmlspecialchars($mensaje) ?></p>
            <?php endif; ?>

            <?php if($errores): ?>
                <ul class="highlight">
                    <?php foreach($errores as $campo => $error): ?>
                        <li><?= htmlspecialchars("$campo: $error") ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post">
                <label for="matricula">Matrícula</label>
                <input type="text" name="matricula" placeholder="Matrícula" required>

                <label for="marca">Marca</label>
                <input type="text" name="marca" placeholder="Marca" required>

                <label for="modelo">Modelo</label>
                <input type="text" name="modelo" placeholder="Modelo" required>

                <label for="anio">Año</label>
                <input type="number" name="anio" placeholder="Año" min="1900" max="<?= date('Y') ?>" required>

                <label for="color">Color</label>
                <input type="text" name="color" placeholder="Color" required>

                <label for="numeroPlazas">Número de plazas</label>
                <input type="number" name="numeroPlazas" placeholder="Número de plazas" min="2" max="9" required>

                <label for="tipoPropulsion">Tipo de Propulsión</label>
                <select name="tipoPropulsion" required>
                    <option value="">-- Tipo de Propulsión --</option>
                    <?php foreach($tiposPropulsion as $tipo): ?>
                        <option value="<?= $tipo ?>"><?= $tipo ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="transmision">Transmisión</label>
                <select name="transmision" required>
                    <option value="">-- Transmisión --</option>
                    <?php foreach($transmisiones as $tr): ?>
                        <option value="<?= $tr ?>"><?= $tr ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="idCategoria">Categoría</label>
                <select name="idCategoria" required>
                    <option value="">-- Seleccione categoría --</option>
                    <?php foreach($categorias as $cat): ?>
                        <option value="<?= $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nombreCategoria']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="hidden" name="idEstado" value="<?= $estadoPorDefecto ?>">

                <label for="kmInicial">Kilómetros iniciales</label>
                <input type="number" name="kmInicial" placeholder="Kilómetros iniciales" min="0" required>

                <label for="fechaUltimaRevision">Fecha última revisión</label>
                <input type="date" name="fechaUltimaRevision" placeholder="Fecha última revisión">

                <label for="precioAdquisicion">Precio de adquisición (€)</label>
                <input type="number" step="0.01" name="precioAdquisicion" placeholder="Precio adquisición">

                <label for="fechaAdquisicion">Fecha de adquisición</label>
                <input type="date" name="fechaAdquisicion" placeholder="Fecha adquisición">

                <label for="notasInternas">Notas internas</label>
                <textarea name="notasInternas" placeholder="Notas internas"></textarea>

                <button type="submit" class="primary">Dar de Alta Vehículo</button>
            </form>
        </div>
    </div>
</body>
</html>
