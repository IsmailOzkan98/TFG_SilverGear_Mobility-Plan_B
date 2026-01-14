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

$stmt = $pdo->prepare("
    SELECT idReserva, fechaInicio, fechaFin, estadoReserva
    FROM Reserva
    WHERE idUsuario = ?
      AND estadoReserva = 'Pendiente'
    ORDER BY fechaInicio ASC
");
$stmt->execute([$idUsuario]);
$reservasPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT idReserva, fechaInicio, fechaFin, estadoReserva
    FROM Reserva
    WHERE idUsuario = ?
      AND estadoReserva <> 'Pendiente'
    ORDER BY fechaInicio DESC
");
$stmt->execute([$idUsuario]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <a href="tiendaAlquiler.php" class="btn btn-custom">Volver a tienda</a>
                    <a href="editarPerfil.php?dni=<?= urlencode($usuario['dni']) ?>" class="btn btn-custom">Editar perfil</a>
                </div>


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
                                        <?= $r['estadoReserva'] ?>
                                    </span>
                                    <div class="d-flex gap-2">
                                        <a href="editarReserva.php?id=<?= $r['idReserva'] ?>" class="btn btn-sm btn-custom">
                                            Editar
                                        </a>
                                        <a href="cancelarReserva.php?id=<?= $r['idReserva'] ?>" class="btn btn-sm btn-custom">
                                            Cancelar
                                        </a>
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
                                <?= $h['estadoReserva'] ?>
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