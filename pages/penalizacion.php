<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'ventas', 'mecanico']);

$pdo = getPDO();
$usuarioSesion = $_SESSION['usuario'];
$dniUsuario = $usuarioSesion['dni'];

if (isset($_GET['success'], $_GET['id'])) {
    $idPagado = (int)$_GET['id'];
    $stmt = $pdo->prepare("
        UPDATE Penalizacion 
        SET estadoPenalizacion = 'PAGADO' 
        WHERE idPenalizacion = :id
    ");
    $stmt->execute([':id' => $idPagado]);

    // Redirigir para limpiar la URL y evitar re-ejecución
    header("Location: penalizacion.php");
    exit;
}

// Obtener todas las penalizaciones del usuario
$stmt = $pdo->prepare("
    SELECT p.idPenalizacion, p.matriculaVehiculo, p.idReserva, p.cantidad, p.nota, p.fechaRegistro, p.estadoPenalizacion
    FROM Penalizacion p
    WHERE p.dniCliente = :dni
    ORDER BY p.fechaRegistro DESC
");
$stmt->execute([':dni' => $dniUsuario]);
$penalizaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Penalizaciones</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<section class="bgblock" style="background-image: url('../images/backgroundPenalizacion.jpg'); max-width: 1400px;">
    <div class="bgblock-content d-flex flex-column align-items-center" style="min-height: 600px; gap: 30px;">
        <div class="glass" style="max-width: 900px; width: 100%; padding: 30px;">
            <h1 class="display-5 fw-bold mb-3 text-center">Mis Penalizaciones</h1>

            <?php if (empty($penalizaciones)): ?>
                <p class="text-center text-muted">No tienes penalizaciones.</p>
                <div class="text-center mt-4">
                    <a href="miPerfil.php" class="btn btn-custom">Volver a mi perfil</a>
                </div>
            <?php else: ?>
                <div class="table-responsive glass p-3">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Reserva #</th>
                                <th>Cantidad (€)</th>
                                <th>Nota</th>
                                <th>Fecha registro</th>
                                <th>Estado / Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($penalizaciones as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['matriculaVehiculo']) ?></td>
                                    <td><?= $p['idReserva'] ?></td>
                                    <td><?= number_format($p['cantidad'], 2) ?> €</td>
                                    <td><?= htmlspecialchars($p['nota']) ?></td>
                                    <td><?= $p['fechaRegistro'] ?></td>
                                    <td>
                                        <?php if ($p['estadoPenalizacion'] === 'PENDIENTE'): ?>
                                            <button class="btn btn-custom" data-id="<?= $p['idPenalizacion'] ?>">Pagar</button>
                                        <?php else: ?>
                                            <span class="btn btn-custom">PAGADO</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('<?= STRIPE_PUBLISHABLE_KEY ?>');

    document.querySelectorAll('.btn-pagar').forEach(btn => {
        btn.addEventListener('click', () => {
            const idPenalizacion = btn.dataset.id;

            fetch('../includes/crear_pago_penalizacion.php?id=' + idPenalizacion)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    stripe.redirectToCheckout({ sessionId: data.id });
                })
                .catch(err => console.error(err));
        });
    });
</script>
</body>
</html>
