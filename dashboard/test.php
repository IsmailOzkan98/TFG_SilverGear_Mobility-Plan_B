<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico']);
require_once '../includes/controlFlota.php';

$pdo = getPDO();

// 🧪 coger reservas reales NO CUBIERTAS
$stmt = $pdo->query("
    SELECT idReserva, fechaInicio, estado, marcaSolicitada, modeloSolicitado
    FROM Reserva
    WHERE estado = 'NO CUBIERTA'
    ORDER BY idReserva ASC
    LIMIT 10
");

$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Control Flota</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #eee; }
        .ok { color: green; font-weight: bold; }
        .no { color: red; font-weight: bold; }
    </style>
</head>
<body>

<h2>🧪 TEST REAL fechaPermitida() + BD</h2>

<p><strong>Fecha servidor:</strong> <?= date('Y-m-d') ?></p>

<table>
    <tr>
        <th>ID</th>
        <th>Fecha Inicio</th>
        <th>Hoy</th>
        <th>Día anterior</th>
        <th>¿Permite?</th>
        <th>Debug asignación</th>
    </tr>

<?php foreach ($reservas as $r): ?>

<?php
$hoy = date('Y-m-d');
$inicio = date('Y-m-d', strtotime($r['fechaInicio']));
$diaAnterior = date('Y-m-d', strtotime($r['fechaInicio'] . ' -1 day'));

$resultado = fechaPermitida($r['fechaInicio']);

$debugAsignacion = ($resultado)
    ? "SE PODRÍA ASIGNAR"
    : "BLOQUEADA";
?>

<tr>
    <td><?= $r['idReserva'] ?></td>
    <td><?= $inicio ?></td>
    <td><?= $hoy ?></td>
    <td><?= $diaAnterior ?></td>
    <td>
        <?= $resultado ? '<span class="ok">✔ TRUE</span>' : '<span class="no">✖ FALSE</span>' ?>
    </td>
    <td><?= $debugAsignacion ?></td>
</tr>

<?php endforeach; ?>

</table>

</body>
</html>