<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico']);

$pdo = getPDO();


if (!isset($_SESSION['cesta'])) {
    $_SESSION['cesta'] = [];
}


$accion = $_GET['accion'] ?? null;
$idVehiculo = $_GET['id'] ?? null;

if ($accion) {
    switch ($accion) {
        case 'añadir':
            if ($idVehiculo) {
                $_SESSION['cesta'][$idVehiculo] = true;
            }
            break;
        case 'quitar':
            if ($idVehiculo && isset($_SESSION['cesta'][$idVehiculo])) {
                unset($_SESSION['cesta'][$idVehiculo]);
            }
            break;
        case 'vaciar':
            $_SESSION['cesta'] = [];
            break;
        case 'procesar':
            $_SESSION['cesta'] = []; 
            $procesado = true;
            break;
    }
}

$vehiculosCesta = [];
$total = 0;

if (!empty($_SESSION['cesta'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cesta'])));
    $stmt = $pdo->query("
        SELECT idVehiculo, marca, modelo, precioVenta
        FROM Vehiculo
        WHERE idVehiculo IN ($ids)
    ");
    $vehiculosCesta = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($vehiculosCesta as $v) {
        $total += $v['precioVenta'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Cesta</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div id="header-container"></div>

<div class="container my-5">
    <h2 class="mb-4">Mi Cesta</h2>

    <?php if (isset($procesado) && $procesado): ?>
        <div class="alert alert-success">¡Compra procesada con éxito!</div>
    <?php endif; ?>

    <?php if (empty($vehiculosCesta)): ?>
        <p>Tu cesta está vacía.</p>
        <a href="tiendaComprar.php" class="btn btn-custom">Volver a la tienda</a>
    <?php else: ?>
        <div class="glass p-4">
            <table class="table table-striped mb-3">
                <thead>
                    <tr>
                        <th>Vehiculo</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehiculosCesta as $v): ?>
                        <tr>
                            <td><?= htmlspecialchars($v['marca'].' '.$v['modelo']) ?></td>
                            <td><?= number_format($v['precioVenta'], 2) ?> €</td>
                            <td>
                                <a href="cesta.php?accion=quitar&id=<?= $v['idVehiculo'] ?>" class="btn btn-custom btn-sm">Quitar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h4>Total: <?= number_format($total, 2) ?> €</h4>

            <div class="mt-3 d-flex gap-2 flex-wrap">
                <a href="cesta.php?accion=vaciar" class="btn btn-custom">Vaciar cesta</a>
                <a href="cesta.php?accion=procesar" class="btn btn-custom">Procesar compra</a>
                <a href="tiendaComprar.php" class="btn btn-custom">Seguir comprando</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<div id="footer-container"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
