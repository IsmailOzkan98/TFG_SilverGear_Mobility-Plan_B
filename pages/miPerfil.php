<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'mecanico', 'ventas', 'dropoff', 'limpieza', 'cliente']);


$pdo = getPDO();


$usuarioSesion = $_SESSION['usuario'];
$idUsuario = $usuarioSesion['idUsuario'];

$stmt = $pdo->prepare("
    SELECT nombre, apellidos, dni
    FROM Usuario
    WHERE idUsuario = ?
");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die('Usuario no encontrado');
}

// Reservas pendientes
$stmt = $pdo->prepare("
    SELECT r.idReserva, r.fechaInicio, r.fechaFin, r.estado,
           r.marcaSolicitada, r.modeloSolicitado,
           c.nombreCategoria
    FROM Reserva r
    LEFT JOIN Categoria c ON r.idCategoria = c.idCategoria
    WHERE r.idUsuario = ?
      AND r.estado IN ('NO CUBIERTA', 'CUBIERTA')
    ORDER BY r.fechaInicio DESC
");
$stmt->execute([$idUsuario]);
$reservasPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Historial
$stmt = $pdo->prepare("
    SELECT r.idReserva, r.fechaInicio, r.fechaFin, r.estado,
           r.marcaSolicitada, r.modeloSolicitado, r.idCategoria AS categoriaSolicitada,
           v.marca AS marcaVehiculo, v.modelo AS modeloVehiculo,
           c.nombreCategoria AS categoriaVehiculo
    FROM Reserva r
    LEFT JOIN Vehiculo v ON r.idVehiculoAsignado = v.idVehiculo
    LEFT JOIN Categoria c ON v.idCategoria = c.idCategoria
    WHERE r.idUsuario = ?
      AND r.estado <> 'NO CUBIERTA'
    ORDER BY r.fechaInicio DESC
");

$stmt->execute([$idUsuario]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);






$stmt = $pdo->prepare("
    SELECT c.idCompra, v.marca, v.modelo, c.precio, c.fechaCompra
    FROM Compra c
    JOIN Vehiculo v ON c.idVehiculo = v.idVehiculo
    WHERE c.idUsuario = ?
    ORDER BY c.fechaCompra DESC
");
$stmt->execute([$idUsuario]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Mi Perfil</title>

    <!-- Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <section class="bgblock" style="background-image: url('../images/backgorundMiPerfil.png'); max-width: 1400px;">
        <div class="bgblock-content d-flex flex-column align-items-center" style="min-height: 600px; gap: 30px;">

            <div class="glass" style="max-width: 900px; width: 100%; padding: 30px;">

                <h1 class="display-5 fw-bold mb-3 text-center">Mi Perfil</h1>

                <p class="text-center fs-4 fw-semibold mb-1">
                    <?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?>
                </p>

                <p class="text-center text-muted mb-4">
                    DNI: <?= htmlspecialchars($usuario['dni']) ?>
                </p>

                <div class="d-flex justify-content-center gap-3 mb-5">
                    <a href="tiendaAlquiler.php" class="btn btn-custom">Alquilar Vehiculo</a>
                    <a href="tiendaComprar.php" class="btn btn-custom">Comprar Vehiculo</a>
                    <a href="editarPerfil.php?dni=<?= urlencode($usuario['dni']) ?>" class="btn btn-custom">Editar perfil</a>
                    <a href="cesta.php" class="btn btn-custom">Mi Cesta</a>
                </div>

                <?php if ($_SESSION['usuario']['rol'] === 'cliente'): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <a href="confirmarEliminarUsuario.php" class="btn btn-danger">
                            Eliminar mi cuenta
                        </a>
                    </div>

                <?php endif; ?>


                <h3 class="mb-3">Mis reservas</h3>
                <ul class="list-group mb-4">
                    <?php if (empty($reservasPendientes)): ?>
                        <li class="list-group-item text-center text-muted">
                            No tienes reservas pendientes
                        </li>
                    <?php else: ?>
                        <?php foreach ($reservasPendientes as $r): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        Reserva #<?= $r['idReserva'] ?> —
                                        <?= $r['fechaInicio'] ?> / <?= $r['fechaFin'] ?> —
                                        <?= $r['estado'] ?> —
                                        Marca: <?= htmlspecialchars($r['marcaVehiculo'] ?? $r['marcaSolicitada']) ?> —
                                        Modelo: <?= htmlspecialchars($r['modeloVehiculo'] ?? $r['modeloSolicitado']) ?> —
                                        Categoría: <?= htmlspecialchars($r['nombreCategoria'] ?? 'Sin categoría') ?>
                                    </span>
                                    <div class="d-flex gap-2">
                                        <a href="editarReserva.php?id=<?= $r['idReserva'] ?>" class="btn btn-sm btn-custom">
                                            Editar
                                        </a>
                                        <form method="POST" action="../includes/cancelarReserva.php" style="display:inline">
                                            <input type="hidden" name="idReserva" value="<?= $r['idReserva'] ?>">
                                            <button type="submit" class="btn btn-sm btn-custom">Cancelar</button>
                                        </form>

                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

                <h3 class="mb-3">Mi historial de reservas</h3>
                <ul class="list-group mb-3">
                    <?php if (empty($historial)): ?>
                        <li class="list-group-item text-center text-muted">
                            No hay reservas anteriores
                        </li>
                    <?php else: ?>
                        <?php foreach ($historial as $h): ?>
                            <li class="list-group-item">
                                Reserva #<?= $h['idReserva'] ?> —
                                <?= $h['fechaInicio'] ?> / <?= $h['fechaFin'] ?> —
                                <?= $h['estado'] ?> —
                                Marca: <?= htmlspecialchars($h['marcaVehiculo'] ?? $h['marcaSolicitada']) ?> —
                                Modelo: <?= htmlspecialchars($h['modeloVehiculo'] ?? $h['modeloSolicitado']) ?> —
                                Categoría: <?=
                                            htmlspecialchars(
                                                $h['categoriaVehiculo'] ??
                                                    ($h['categoriaSolicitada'] ? getNombreCategoria($h['categoriaSolicitada']) : 'Sin categoría')
                                            )?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>




                <h3 class="mb-3">Mis compras realizadas</h3>

                <ul class="list-group mb-4">
                    <?php if (empty($compras)): ?>
                        <li class="list-group-item text-center text-muted">
                            No has comprado ningún vehículo
                        </li>
                    <?php else: ?>
                        <?php foreach ($compras as $compra): ?>
                            <li class="list-group-item">
                                <?= htmlspecialchars($compra['marca'] . ' ' . $compra['modelo']) ?> —
                                <?= number_format($compra['precio'], 2) ?> € —
                                <?= $compra['fechaCompra'] ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>

            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>