<?php
require_once '../includes/common.php';
require_once '../includes/security.php';
requireRole(['admin', 'cliente']);

$pdo = getPDO();

$usuarioSesion = $_SESSION['usuario'];

if ($usuarioSesion['rol'] === 'cliente') {
    $idUsuario = $usuarioSesion['idUsuario'];
} else {
    if (!isset($_GET['idUsuario'])) {
        die("Usuario no especificado.");
    }
    $idUsuario = intval($_GET['idUsuario']);
}


// Obtener datos del usuario a eliminar
$stmt = $pdo->prepare("SELECT nombre, apellidos, dni FROM Usuario WHERE idUsuario = ?");
$stmt->execute([$idUsuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Procesar formulario de confirmación
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmacion = $_POST['confirmacion'] ?? '';
    if ($confirmacion === 'CONFIRMAR') {
        header("Location: ../includes/eliminarUser.php?idUsuario=$idUsuario");
        exit;
    } else {
        $error = "Debes escribir CONFIRMAR para continuar con la eliminación.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmar Eliminación</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<section class="bgblock" style="background-image: url('../images/backgroundEliminar.jpg'); max-width: 1400px;">
    <div class="bgblock-content d-flex flex-column align-items-center" style="min-height: 500px; gap: 30px;">

        <div class="glass" style="max-width: 500px; width: 100%; padding: 30px; text-align: center;">

            <h1 class="display-5 fw-bold mb-3 text-center">Confirmar Eliminación</h1>

            <p class="fs-5 mb-4">
                Vas a eliminar al usuario: <br>
                <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellidos']) ?></strong><br>
                DNI: <?= htmlspecialchars($usuario['dni']) ?>
            </p>

            <?php if ($error): ?>
                <p class="text-danger mb-3"><?= $error ?></p>
            <?php endif; ?>

            <form method="POST" class="d-flex flex-column gap-2">
                <input type="text" name="confirmacion" placeholder="Escribe CONFIRMAR para continuar" class="form-control text-center">
                <button type="submit" class="btn btn-custom">Eliminar Usuario</button>
                <a href="<?= volverSegunRol() ?>" class="btn btn-custom">Cancelar</a>
            </form>

        </div>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
