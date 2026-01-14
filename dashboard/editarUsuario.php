<?php
session_start();
require_once '../includes/common.php';

$pdo = getPDO();


if (!isset($_GET['dni'])) {
    die('Usuario no especificado');
}

$dni = $_GET['dni'];


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
        $stmt = $pdo->prepare("
            UPDATE Usuario SET
                nombre = :nombre,
                apellidos = :apellidos,
                fechaNacimiento = :fechaNacimiento,
                sexo = :sexo,
                direccion = :direccion,
                ciudad = :ciudad,
                pais = :pais,
                codigoPostal = :codigoPostal,
                telefono = :telefono,
                email = :email,
                fechaCarnet = :fechaCarnet,
                idRol = :idRol
            WHERE dni = :dni
        ");

        $stmt->execute([
            ':nombre' => $_POST['nombre'],
            ':apellidos' => $_POST['apellidos'],
            ':fechaNacimiento' => $_POST['fechaNacimiento'],
            ':sexo' => $_POST['sexo'],
            ':direccion' => $_POST['direccion'],
            ':ciudad' => $_POST['ciudad'],
            ':pais' => $_POST['pais'],
            ':codigoPostal' => $_POST['codigoPostal'],
            ':telefono' => $_POST['telefono'],
            ':email' => $_POST['email'],
            ':fechaCarnet' => $_POST['fechaCarnet'],
            ':idRol' => $_POST['idRol'],
            ':dni' => $dni
        ]);

        $_SESSION['mensaje'] = 'Usuario actualizado correctamente';
        header('Location: dashboardAdmin.php');
        exit;
    } catch (PDOException $e) {
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
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">SilverGear Mobility - Editar Usuario</span>
            <div class="collapse navbar-collapse show">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="dashboardAdmin.php" class="nav-link">Volver</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../includes/logout.php">Cerrar sesión</a>
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

            <div class="col-md-6">
                <label class="form-label">DNI</label>
                <input type="text" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['dni']) ?>" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['nombre']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Apellidos</label>
                <input type="text" name="apellidos" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['apellidos']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha nacimiento</label>
                <input type="date" name="fechaNacimiento" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['fechaNacimiento']) ?>" required>
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
                <label class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['telefono']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['email']) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Dirección</label>
                <input type="text" name="direccion" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['direccion']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Ciudad</label>
                <input type="text" name="ciudad" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['ciudad']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">País</label>
                <input type="text" name="pais" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['pais']) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Código postal</label>
                <input type="text" name="codigoPostal" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['codigoPostal']) ?>">
            </div>

            <div class="col-md-6">
                <label class="form-label">Fecha carnet</label>
                <input type="date" name="fechaCarnet" class="form-control"
                    value="<?= htmlspecialchars($usuarioDB['fechaCarnet']) ?>" required>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>

        </form>
    </div>

</body>

</html>