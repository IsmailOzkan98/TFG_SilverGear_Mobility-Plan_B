<?php
require_once '../includes/common.php';

$pdo = getPDO();

// Contraseña común
$passComun = '12345678c';
$hashPass = password_hash($passComun, PASSWORD_DEFAULT);

// Roles
$stmt = $pdo->prepare("SELECT idRol FROM Rol WHERE nombreRol = 'cliente'");
$stmt->execute();
$idRolCliente = $stmt->fetchColumn();

if (!$idRolCliente) {
    die("No se encontró el rol 'cliente'.");
}

// Datos de ejemplo para generar 20 clientes
$nombres = ['Carlos', 'Ana', 'Luis', 'Marta', 'Pedro', 'Lucia', 'Javier', 'Sofia', 'Alberto', 'Laura'];
$apellidos = ['Gomez', 'Perez', 'Lopez', 'Sanchez', 'Diaz', 'Moreno', 'Romero', 'Fernandez', 'Torres', 'Jimenez'];

for ($i = 1; $i <= 20; $i++) {
    $nombre = $nombres[array_rand($nombres)];
    $apellido = $apellidos[array_rand($apellidos)];
    $dni = sprintf('%08d%s', rand(10000000, 99999999), chr(rand(65, 90))); // Ej: 12345678A
    $direccion = rand(1, 200) . " Calle Falsa";
    $ciudad = "Ciudad" . rand(1, 10);
    $pais = "España";
    $cp = str_pad(rand(10000, 52999), 5, '0', STR_PAD_LEFT);
    $telefono = '6' . rand(10000000, 99999999);
    $email = strtolower($nombre . $apellido . $i . '@example.com');
    $fechaNacimiento = date('Y-m-d', strtotime('-' . rand(18, 60) . ' years'));
    $fechaCarnet = date('Y-m-d', strtotime($fechaNacimiento . ' +18 years'));
    $sexo = (rand(0,1) ? 'Hombre' : 'Mujer');

    $stmt = $pdo->prepare("
        INSERT INTO Usuario 
        (nombre, apellidos, dni, direccion, ciudad, pais, codigoPostal, telefono, email, fechaNacimiento, fechaCarnet, contrasena, idRol, sexo)
        VALUES
        (:nombre, :apellidos, :dni, :direccion, :ciudad, :pais, :cp, :telefono, :email, :fechaNacimiento, :fechaCarnet, :contrasena, :idRol, :sexo)
    ");

    $stmt->execute([
        ':nombre' => $nombre,
        ':apellidos' => $apellido,
        ':dni' => $dni,
        ':direccion' => $direccion,
        ':ciudad' => $ciudad,
        ':pais' => $pais,
        ':cp' => $cp,
        ':telefono' => $telefono,
        ':email' => $email,
        ':fechaNacimiento' => $fechaNacimiento,
        ':fechaCarnet' => $fechaCarnet,
        ':contrasena' => $hashPass,
        ':idRol' => $idRolCliente,
        ':sexo' => $sexo
    ]);

    echo "Cliente $i creado: $nombre $apellido, email: $email\n";
}

echo "¡20 clientes generados correctamente!";
