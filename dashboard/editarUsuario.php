<?php
require_once '../includes/common.php';
require_once '../includes/usuario.php';
require_once '../includes/security.php';
requireRole(['admin', 'ventas']);

$pdo = getPDO();


if (!isset($_GET['dni'])) {
    die('Usuario no especificado');
}

$dni = $_GET['dni'];

//para selector de roles
$rolActual = getUserRole();
$isAdmin = ($rolActual === 'admin');
$isVentas = ($rolActual === 'ventas');

//consultas
$stmt = $pdo->prepare("SELECT * FROM Usuario WHERE dni = :dni");
$stmt->execute([':dni' => $dni]);
$usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);
$stmtRoles = $pdo->query("SELECT idRol, nombreRol FROM Rol ORDER BY nombreRol");
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

if (!$usuarioDB) {
    die('Usuario no encontrado');
}

$errores = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $usuario = new Usuario($_POST, $pdo, false);
        $usuario->dni = $dni;

        $resultado = $usuario->actualizar();

        if (isset($resultado['exito'])) {
            $_SESSION['mensaje'] = 'Usuario actualizado correctamente';
            header('Location: ' . volverSegunRol());
            exit;
        }

        $errores = $resultado['errores'] ?? [];
    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php imprimirFavicon(); ?>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Editar Usuario</span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="<?= volverSegunRol() ?>" class="nav-link">Volver</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Editar usuario</h2>

        <?php if (!empty($errores['general'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($errores['general']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="row g-3">

            <?php if ($isAdmin): ?>
                <div class="col-md-6">
                    <label class="form-label">Rol</label>
                    <select name="idRol" class="form-select" required>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['idRol'] ?>"
                                <?= $rol['idRol'] == $usuarioDB['idRol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombreRol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php elseif ($isVentas): ?>

                <div class="col-md-6">
                    <label class="form-label">Rol</label>

                    <select class="form-select" disabled>
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= $rol['idRol'] ?>"
                                <?= $rol['idRol'] == $usuarioDB['idRol'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rol['nombreRol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="hidden" name="idRol" value="<?= $usuarioDB['idRol'] ?>">
                </div>

            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label">DNI</label>
                <input type="text" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['dni']) ?>" readonly disabled>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['nombre']) ?>" required>

                <?php if (!empty($errores['nombre'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['nombre']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Apellidos</label>
                <input type="text" name="apellidos" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['apellidos']) ?>" required>

                <?php if (!empty($errores['apellidos'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['apellidos']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha nacimiento</label>
                <input type="date" name="fechaNacimiento" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['fechaNacimiento']) ?>" required>

                <?php if (!empty($errores['fechaNacimiento'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['fechaNacimiento']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Sexo</label>
                <select name="sexo" class="form-select">
                    <option value="">Seleccionar</option>
                    <?php foreach (['Masculino', 'Femenino', 'Otro'] as $sexo): ?>
                        <option value="<?= $sexo ?>" <?= $usuarioDB['sexo'] === $sexo ? 'selected' : '' ?>>
                            <?= $sexo ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Telefono</label>
                <input type="text" name="telefono" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['telefono']) ?>" required>

                <?php if (!empty($errores['telefono'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['telefono']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['email']) ?>" required>

                <?php if (!empty($errores['email'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['email']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Direccion</label>
                <input type="text" name="direccion" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['direccion']) ?>">

                <?php if (!empty($errores['direccion'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['direccion']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ciudad</label>
                <input type="text" name="ciudad" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['ciudad']) ?>">

                <?php if (!empty($errores['ciudad'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['ciudad']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <label class="form-label">Pais</label>
                <input type="text" name="pais" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['pais']) ?>">

                <?php if (!empty($errores['pais'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['pais']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <label class="form-label">Codigo postal</label>
                <input type="text" name="codigoPostal" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['codigoPostal']) ?>">

                <?php if (!empty($errores['codigoPostal'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['codigoPostal']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha carnet</label>
                <input type="date" name="fechaCarnet" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['fechaCarnet']) ?>" required>

                <?php if (!empty($errores['fechaCarnet'])): ?>
                    <span class="error" style="color:red">
                        <?= htmlspecialchars($errores['fechaCarnet']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>

        </form>
    </div>

</body>

</html>