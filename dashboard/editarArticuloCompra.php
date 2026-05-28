<?php
require_once '../includes/common.php';
require_once '../includes/security.php';

requireRole(['admin', 'ventas']);

$pdo = getPDO();
$mensaje = '';
$errores = [];

// Obtener ID
$idVehiculo = $_GET['idVehiculo'] ?? null;
if (!$idVehiculo) {
    die("Artículo no especificado.");
}

// Cargar datos
$stmt = $pdo->prepare("
    SELECT idVehiculo, marca, modelo, precioVenta, notasInternas
    FROM Vehiculo
    WHERE idVehiculo = :idVehiculo
    LIMIT 1
");
$stmt->execute([':idVehiculo' => $idVehiculo]);
$vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vehiculo) {
    die("Artículo no encontrado.");
}

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $precioVenta = $_POST['precioVenta'] ?? null;
        $notasInternas = $_POST['notasInternas'] ?? null;

        if ($precioVenta === null || $precioVenta < 0) {
            throw new Exception("El precio no es válido.");
        }

        $stmt = $pdo->prepare("
            UPDATE Vehiculo
            SET precioVenta = :precioVenta,
                notasInternas = :notasInternas
            WHERE idVehiculo = :idVehiculo
        ");

        $stmt->execute([
            ':precioVenta' => $precioVenta,
            ':notasInternas' => $notasInternas,
            ':idVehiculo' => $idVehiculo
        ]);

        $vehiculo['precioVenta'] = $precioVenta;
        $vehiculo['notasInternas'] = $notasInternas;

        $mensaje = "Artículo actualizado correctamente.";

    } catch (Exception $e) {
        $errores[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo de Compra</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <?php imprimirFavicon(); ?>
</head>

<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Editar articulo · <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a href="../pages/tiendaComprar.php" class="nav-link">Volver a la tienda</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container">
    <div class="card">
        <div class="card-body">

            <?php if ($mensaje): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <?php if ($errores): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errores as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Precio de venta (€)</label>
                    <input type="number"
                           step="0.01"
                           min="0"
                           name="precioVenta"
                           class="form-control"
                           value="<?= htmlspecialchars($vehiculo['precioVenta']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="notasInternas"
                              class="form-control"
                              rows="4"><?= htmlspecialchars($vehiculo['notasInternas']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>

                <a href="../pages/tiendaComprar.php"
                   class="btn btn-secondary ms-2">
                    Cancelar
                </a>
            </form>

        </div>
    </div>
</div>

</body>
</html>
