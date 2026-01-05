<?php
require_once 'db.php';

//iniciar session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getPDO()
{
    static $pdo = null; //conexion una vez
    if ($pdo === null) {
        $pdo = conectar(); 
    }
    return $pdo;
}

//Validaciones
function validarNombre($nombre)
{
    if (empty($nombre)) return "El nombre es obligatorio.";
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,}$/u", $nombre)) {
        return "El nombre puede tener solo letras y espacios";
    }
    return true;
}


function validarApellidos($apellidos)
{
    if (empty($apellidos)) return "Los apellidos son obligatorios.";
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,}$/u", $apellidos)) {
        return "Los apellidos pueden tener solo letras y espacios";
    }
    return true;
}


function validarDNI($dni)
{
    $dni = strtoupper(str_replace([' ', '-'], '', $dni));

    //DNI: 8 num mas letra
    if (preg_match("/^[0-9]{8}[A-Z]$/", $dni)) {
        return true; // 
    }
    //NIE: X/Y/Z + 7 u 8 nums y letra final
    elseif (preg_match("/^[XYZ][0-9]{7,8}[A-Z]$/", $dni)) {
        return true; // 
    }
    return "Formato de DNI/NIE no es correcto.";
}


function validarFecha($fecha)
{
    if (empty($fecha)) return "La fecha es obligatoria!";
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)) return "Formato de fecha invalido.";
    if (strtotime($fecha) > time()) return "La fecha no puede ser futura.";
    return true;
}

function validarTexto($texto, $campo) //ciudad y pais
{
    if (empty($texto)) return "$campo es obligatorio.";
    if (strlen($texto) < 2) return "$campo demasiado corto.";
    return true;
}

function validarCodigoPostal($cp)
{
    if (empty($cp)) return true; // opcional
    if (!preg_match("/^\d{5}$/", $cp)) return "Codigo postal es invalido.";
    return true;
}

function validarTelefono($telefono)
{
    if (empty($telefono)) return "El teléfono es obligatorio!";
    $numero = preg_replace("/\D/", "", $telefono); // quitar todo lo que no sea un numero
    if (strlen($numero) < 9) return "Telefono es invalido.";
    return true;
}

function validarContrasena($pass)
{
    if (empty($pass)) return "La contraseña es obligatoria!";
    if (strlen($pass) < 8) return "La contraseña debe tener al menos 8 caracteres!";
    if (!preg_match("/[A-Za-z]/", $pass) || !preg_match("/\d/", $pass)) {
        return "La contraseña debe contener letras y numeros.";
    }
    return true; 
}

function hashContrasena($pass)
{
    return password_hash($pass, PASSWORD_DEFAULT);
}

function validarContrasenaRepetida($pass, $repetir)
{
    if ($pass !== $repetir) return "Las contraseñas no coinciden.";
    return true;
}

function validarEmail($email) {
    if (empty($email)) {
        return "El email es obligatorio.";
    }

    // Validar formato correcto
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Formato de email inválido.";
    }

    return true; // Email válido
}

