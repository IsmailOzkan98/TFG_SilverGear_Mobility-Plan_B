<?php
require_once '../includes/common.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------
// Redirección si ya está logueado
// ------------------------
if (isset($_SESSION['usuario']['rol'])) {
    $rol = $_SESSION['usuario']['rol'];
    if ($rol === 'cliente') {
        header("Location: tiendaComprar.php");
    } else {
        header("Location: ../dashboard/dashboard.php");
    }
    exit;
}

// ------------------------
// Lógica de login
// ------------------------
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    // Validaciones
    if (validarEmail($email) !== true || empty($password)) {
        $error = "Email o contraseña invalidos";
    } else {

        $pdo = getPDO();

        $stmt = $pdo->prepare("
            SELECT u.idUsuario, u.nombre, u.apellidos, u.contrasena, r.nombreRol
            FROM Usuario u
            JOIN Rol r ON u.idRol = r.idRol
            WHERE u.email = ?
        ");

        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['contrasena'])) {

            // Guardar sesión
            $_SESSION['usuario'] = [
                'idUsuario' => $usuario['idUsuario'],
                'nombre' => $usuario['nombre'],
                'apellidos' => $usuario['apellidos'],
                'rol' => $usuario['nombreRol']
            ];

            // Redirección según rol
            if ($usuario['nombreRol'] === 'cliente') {
                header("Location: tiendaComprar.php");
            } else {
                header("Location: ../dashboard/dashboard.php");
            }
            exit;

        } else {
            $error = "Email o contraseña incorrectos";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body> 
    <!-- Contenedores -->
    <div id="header-container"></div>
    <div id="main-container">
        <section class="bgblock" style="background-image: url('../images/backgroundLogin.jpg'); max-width: 1400px;">

    <div class="bgblock-content d-flex justify-content-center align-items-center" style="min-height: 500px;">

        <div class="glass" style="max-width: 500px; width: 100%;">
            <h1 class="display-5 fw-bold mb-4 text-center">Inicia Sesión</h1>

            <!-- mensaje de error PHP -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger text-center">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            
            <form class="d-flex flex-column" method="POST" action="login.php">

                <input type="email" name="email" class="form-control mb-3" placeholder="Tu email" required>
                <input type="password" name="password" class="form-control mb-3" placeholder="Contraseña" required>

                
                <button type="submit" class="btn btn-custom mt-2">
                    Iniciar Sesión
                </button>

                
                <button type="button" class="btn btn-custom mt-2" onclick="loadPage('pages/recuperar.php')">
                    Recuperar Contraseña
                </button>

                <button type="button" class="btn btn-custom mt-2" onclick="loadPage('pages/registrar.php')">
                    No tengo cuenta
                </button>
            </form>

        </div>

    </div>

</section>
    </div>
    <div id="extra-container"></div>
    <div id="footer-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
