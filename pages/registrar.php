<?php
session_start();
require_once '../includes/common.php';
require_once '../includes/usuario.php';

$errores = $_SESSION['errores_registro'] ?? [];
$datos = $_SESSION['datos_registro'] ?? [];
unset($_SESSION['errores_registro'], $_SESSION['datos_registro']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = $_POST;
    $pdo = getPDO();

    try {
        $usuario = new Usuario($datos, $pdo);
        $resultado = $usuario->guardar();

        if (isset($resultado['exito']) && $resultado['exito'] === true) {
            $_SESSION['mensaje'] = "Usuario registrado correctamente.";
            header('Location: login.php'); 
            exit;
        } else {
            $errores = $resultado['errores'];
            $_SESSION['errores_registro'] = $errores;
            $_SESSION['datos_registro'] = $datos;
        }
    } catch (Exception $e) {
        $errores['general'] = $e->getMessage();
        $_SESSION['errores_registro'] = $errores;
        $_SESSION['datos_registro'] = $datos;
    }
}
?>





<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SilverGear Mobility</title>

    <!-- BootStrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet"> <!-- Tipografias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->

    <!-- Estilos CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <!-- Contenedores -->
    <div id="header-container"></div>
    <div id="main-container">
        <section class="bgblock" style="background-image: url('../images/backgorundRegister.jpg'); max-width: 1400px;">
            <div class="bgblock-content d-flex justify-content-center align-items-center" style="min-height: 500px;">
                <div class="glass" style="max-width: 600px; width: 100%;">
                    <h1 class="display-5 fw-bold mb-4 text-center">Registrar</h1>

                    <?php if (!empty($errores['general'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($errores['general']) ?></div>
                    <?php endif; ?>

                    <form id="formRegistro" class="row g-3" method="POST">
                        <?php
                        $campos = [
                            'nombre' => 'Nombre',
                            'apellidos' => 'Apellidos',
                            'dni' => 'DNI/NIE',
                            'fechaNacimiento' => 'Fecha Nac.',
                            'sexo' => 'Sexo',
                            'direccion' => 'Dirección',
                            'ciudad' => 'Ciudad',
                            'pais' => 'País',
                            'codigoPostal' => 'C.Postal',
                            'telefono' => 'Teléfono',
                            'email' => 'Email',
                            'fechaCarnet' => 'Fecha Carnet',
                            'contrasena' => 'Contraseña',
                            'repetirContrasena' => 'R. Contraseña'
                        ];

                        $contacto = ['nombre', 'apellidos', 'dni', 'fechaNacimiento', 'direccion', 'ciudad', 'pais', 'codigoPostal', 'telefono', 'email', 'fechaCarnet'];
                        foreach ($contacto as $campo):
                            $tipo = in_array($campo, ['fechaNacimiento', 'fechaCarnet']) ? 'date' : (in_array($campo, ['email']) ? 'email' : 'text');
                        ?>
                            <div class="col-12 d-flex align-items-center">
                                <label for="<?= $campo ?>" class="form-label me-2" style="min-width:120px;"><?= $campos[$campo] ?>*</label>
                                <div style="width:100%">
                                    <input type="<?= $tipo ?>" class="form-control" id="<?= $campo ?>" name="<?= $campo ?>" value="<?= htmlspecialchars($datos[$campo] ?? '') ?>" placeholder="<?= $campos[$campo] ?>" required>
                                    <?php if (!empty($errores[$campo])): ?>
                                        <span class="error" style="color:red"><?= htmlspecialchars($errores[$campo]) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Sexo -->
                        <div class="col-12 d-flex align-items-center">
                            <label for="sexo" class="form-label me-2" style="min-width:120px;">Sexo</label>
                            <div style="width:100%">
                                <select class="form-control" id="sexo" name="sexo">
                                    <option value="">Selecciona</option>
                                    <option value="Masculino" <?= (isset($datos['sexo']) && $datos['sexo'] == 'Masculino') ? 'selected' : '' ?>>Masculino</option>
                                    <option value="Femenino" <?= (isset($datos['sexo']) && $datos['sexo'] == 'Femenino') ? 'selected' : '' ?>>Femenino</option>
                                    <option value="Otro" <?= (isset($datos['sexo']) && $datos['sexo'] == 'Otro') ? 'selected' : '' ?>>Otro</option>
                                </select>
                                <?php if (!empty($errores['sexo'])): ?>
                                    <span class="error" style="color:red"><?= htmlspecialchars($errores['sexo']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contraseñas -->
                        <?php foreach (['contrasena', 'repetirContrasena'] as $campo): ?>
                            <div class="col-12 d-flex align-items-center">
                                <label for="<?= $campo ?>" class="form-label me-2" style="min-width:120px;"><?= $campos[$campo] ?>*</label>
                                <div style="width:100%">
                                    <input type="password" class="form-control" id="<?= $campo ?>" name="<?= $campo ?>" placeholder="<?= $campos[$campo] ?>" required>
                                    <?php if (!empty($errores[$campo])): ?>
                                        <span class="error" style="color:red"><?= htmlspecialchars($errores[$campo]) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="col-12 text-center mt-3">
                            <a href="login.php" class="btn btn-custom">Ya tengo cuenta</a>
                            <button type="submit" class="btn btn-custom">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
    <div id="extra-container">
        <div class="divider"></div>

        <div id="multiCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">

                <div class="carousel-item active">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0A-Economy.png" class="slider-img" alt="A-Economy">
                            <label>A-Economy</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0B-Compact.png" class="slider-img" alt="B-Compact">
                            <label>B-Compact</label>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0C-Intermediate.png" class="slider-img" alt="C-Intermediate">
                            <label>C-Intermediate</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0D-SUV.png" class="slider-img" alt="D-SUV">
                            <label>D-SUV</label>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0E-Premium.png" class="slider-img" alt="E-Premium">
                            <label>E-Premium</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0F-Van.png" class="slider-img" alt="F-Van">
                            <label>F-Van</label>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="d-flex justify-content-center gap-3">
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0G-Cargo.png" class="slider-img" alt="G-Cargo">
                            <label>G-Cargo</label>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <img src="../images/0H-Classic.png" class="slider-img" alt="H-Classic">
                            <label>H-Classic</label>
                        </div>
                    </div>
                </div>

            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#multiCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#multiCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
    <div id="footer-container"></div>

    <!-- JS loader -->
    <!-- <script src="js/loader.js"></script> -->
    <!-- <script src="js/registro.js"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script para showcase -->
    <!--  <script src="js/showcase.js"></script>  -->

</body>



</html>