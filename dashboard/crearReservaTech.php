<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'ventas']);
require_once '../includes/controlFlota.php';

$pdo = getPDO();
$mensaje = '';
$errores = [];

// Consulta para traer categorias
$categorias = $pdo->query("SELECT idCategoria, nombreCategoria, precioBase, incrementoSeguro, recargoCarnetJoven FROM Categoria")->fetchAll();

// POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dniCliente = trim($_POST['dniCliente']);
    $stmt = $pdo->prepare("SELECT idUsuario, fechaCarnet FROM Usuario WHERE dni = :dni LIMIT 1");
    $stmt->execute([':dni' => $dniCliente]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $errores['dni'] = "Cliente con DNI $dniCliente no encontrado.";
    } else {
        $idUsuario = $usuario['idUsuario'];
        $fechaCarnet = $usuario['fechaCarnet'] ?? null;

        $aplicaRecargoCarnetJoven = false;
        if ($fechaCarnet) {
            $fechaCarnetDT = new DateTime($fechaCarnet);
            $haceDosAnios = (new DateTime())->sub(new DateInterval('P2Y'));
            $aplicaRecargoCarnetJoven = $fechaCarnetDT > $haceDosAnios;
        }

        $idCategoria = $_POST['idCategoria'];
        $marcaSolicitada = trim($_POST['marcaSolicitada'] ?? 'INDEFERENTE');
        $modeloSolicitado = trim($_POST['modeloSolicitado'] ?? 'INDEFERENTE');
        $fechaInicio = $_POST['fechaInicio'];
        $fechaFin = $_POST['fechaFin'];
        $seguro = isset($_POST['seguro']) ? 1 : 0;

        $cat = array_filter($categorias, fn($c) => $c['idCategoria'] == $idCategoria);
        $cat = array_shift($cat);
        $precioBase = $cat['precioBase'] ?? 0;
        $incrementoSeguro = $cat['incrementoSeguro'] ?? 0;
        $recargoCarnet = ($aplicaRecargoCarnetJoven) ? ($cat['recargoCarnetJoven'] ?? 0) : 0;

        $dias = (new DateTime($fechaFin))->diff(new DateTime($fechaInicio))->days + 1;
        if ($dias < 1) $dias = 1;

        $precioDia = $precioBase * (1 + $seguro * $incrementoSeguro / 100 + $recargoCarnet / 100);
        $precioTotal = $precioDia * $dias;

        $stmt = $pdo->prepare("
            INSERT INTO Reserva 
            (idUsuario, idCategoria, marcaSolicitada, modeloSolicitado, fechaInicio, fechaFin, precioDia, precioTotal, estado, seguro, carnetJoven)
            VALUES 
            (:idUsuario, :idCategoria, :marca, :modelo, :fechaInicio, :fechaFin, :precioDia, :precioTotal, 'NO CUBIERTA', :seguro, :carnetJoven)
        ");
        $stmt->execute([
            ':idUsuario' => $idUsuario,
            ':idCategoria' => $idCategoria,
            ':marca' => $marcaSolicitada,
            ':modelo' => $modeloSolicitado,
            ':fechaInicio' => $fechaInicio,
            ':fechaFin' => $fechaFin,
            ':precioDia' => $precioDia,
            ':precioTotal' => $precioTotal,
            ':seguro' => $seguro,
            ':carnetJoven' => $recargoCarnet > 0 ? 1 : 0
        ]);

        $idReserva = $pdo->lastInsertId();
        $asignado = asignarVehiculoAReserva($idReserva);



        if ($asignado) {
            $mensaje = "Reserva creada correctamente para $dniCliente y vehiculo asignado automaticamente.";
        } else {
            $mensaje = "Reserva creada correctamente para $dniCliente. No hay vehiculo disponible para asignar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php imprimirFavicon(); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reserva en nombre de Cliente</title>
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
                <h1 class="mb-4">Crear Reserva</h1>

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

                <form id="formReserva" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">DNI del Cliente</label>
                            <input type="text" class="form-control" name="dniCliente" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="idCategoria" required>
                                <option value="">-- Seleccione categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['idCategoria'] ?>"><?= htmlspecialchars($cat['nombreCategoria']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Marca solicitada</label>
                            <input type="text" class="form-control" name="marcaSolicitada" value="INDEFERENTE">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modelo solicitado</label>
                            <input type="text" class="form-control" name="modeloSolicitado" value="INDEFERENTE">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" name="fechaInicio" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="fechaFin" required>
                        </div>
                        <div class="col-12 form-check mt-2">
                            <input type="checkbox" class="form-check-input" name="seguro" id="seguro">
                            <label class="form-check-label" for="seguro">Seguro todo riesgo</label>
                        </div>
                        <div class="col-12 mt-3">
                            <p><strong>Precio/día:</strong> <span id="precioDia">0.00</span> €</p>
                            <p><strong>Precio total:</strong> <span id="precioTotal">0.00</span> €</p>
                        </div>
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary">Crear Reserva</button>
                            <a href="<?= volverSegunRol() ?>" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('formReserva');
        const precioDiaEl = document.getElementById('precioDia');
        const precioTotalEl = document.getElementById('precioTotal');
        let categorias = <?= json_encode($categorias) ?>;

        function actualizarPrecio() {
            let catId = form.idCategoria.value;
            let cat = categorias.find(c => c.idCategoria == catId);
            if (!cat) return;

            let seguro = form.seguro.checked ? parseFloat(cat.incrementoSeguro) : 0;
            let carnet = parseFloat(cat.recargoCarnetJoven);

            let fechaInicio = new Date(form.fechaInicio.value);
            let fechaFin = new Date(form.fechaFin.value);
            let dias = (fechaFin - fechaInicio) / (1000 * 60 * 60 * 24) + 1;
            if (isNaN(dias) || dias < 1) dias = 1;

            let precioDia = parseFloat(cat.precioBase) * (1 + seguro / 100 + carnet / 100);
            let precioTotal = precioDia * dias;

            precioDiaEl.textContent = precioDia.toFixed(2);
            precioTotalEl.textContent = precioTotal.toFixed(2);
        }

        form.addEventListener('change', actualizarPrecio);
        form.addEventListener('input', actualizarPrecio);
    </script>

</body>

</html>