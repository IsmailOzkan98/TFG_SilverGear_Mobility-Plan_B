<?php
require_once 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../weather.php";
$weather = getWeather();

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


// function validarDNI($dni)
// {
//     $dni = strtoupper(str_replace([' ', '-'], '', $dni));

//     //DNI: 8 num mas letra
//     if (preg_match("/^[0-9]{8}[A-Z]$/", $dni)) {
//         return true; // 
//     }
//     //NIE: X/Y/Z + 7 u 8 nums y letra final
//     elseif (preg_match("/^[XYZ][0-9]{7,8}[A-Z]$/", $dni)) {
//         return true; // 
//     }
//     return "Formato de DNI/NIE no es correcto.";
// }

function validarDNI(PDO $pdo, $dni, $existeEnDB = true)
{
    $dni = strtoupper(str_replace([' ', '-'], '', $dni));

    // validar formato
    if (!preg_match("/^[0-9]{8}[A-Z]$/", $dni) && !preg_match("/^[XYZ][0-9]{7,8}[A-Z]$/", $dni)) {
        return "Formato de DNI o NIE introducido no es adecuado";
    }

    // Comprobar su existencia en bd
    if ($existeEnDB) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE dni = :dni");
        $stmt->execute([':dni' => $dni]);
        if ($stmt->fetchColumn() > 0) {
            return "El DNI/NIE ya esta registrado!";
        }
    }

    return true;
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

// function validarEmail($email) {
//     if (empty($email)) {
//         return "El email es obligatorio!";
//     }


//     if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
//         return "Formato de email introducido no es valido";
//     }

//     return true; 
// }

// Validar email Con verificacion de si existe ya
function validarEmail($email, PDO $pdo, $existeEnDB = true)
{
    if (empty($email)) return "El email es obligatorio!";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Formato de email introducido no es valido";
    }

    // Comprobar si ya existe
    if ($existeEnDB) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Usuario WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            return "El email ya esta registrado.";
        }
    }

    return true;
}

//VEHICULO

function actualizarDisponibilidadVehiculo(&$vehiculo)
{
    switch ($vehiculo->idEstado) {
        case 3: // IMPRO
        case 5: // BAJA
            $vehiculo->disponibilidad = false;
            break;
        case 1: // LIMPIO
        case 2: // SUCIO
        case 4: // VENTAS
            $vehiculo->disponibilidad = true;
            break;
        default:
            $vehiculo->disponibilidad = false;
            break;
    }
}


function registrarHistorialVehiculo(PDO $pdo, $matricula, $dniTrabajador, $accion, $descripcion = '')
{
    
    $stmt = $pdo->prepare("
        INSERT INTO Vehiculo_Historial (idVehiculo, dniTrabajador, accion, descripcion, fechaHora)
        SELECT idVehiculo, :dniTrabajador, :accion, :descripcion, NOW() 
        FROM Vehiculo WHERE matricula = :matricula
    ");
    $stmt->execute([
        ':matricula' => $matricula,
        ':dniTrabajador' => $dniTrabajador,
        ':accion' => $accion,
        ':descripcion' => $descripcion
    ]);
}




function cambiarEstadoVehiculo(PDO $pdo, &$vehiculo, $nuevoEstado, $dniTrabajador = null, $descripcion = '')
{
    

    $vehiculo->idEstado = $nuevoEstado;
    actualizarDisponibilidadVehiculo($vehiculo);

    $stmt = $pdo->prepare("
        UPDATE Vehiculo 
        SET idEstado = :idEstado, disponibilidad = :disponibilidad 
        WHERE matricula = :matricula
    ");
    $stmt->execute([
        ':idEstado' => $vehiculo->idEstado,
        ':disponibilidad' => $vehiculo->disponibilidad ? 1 : 0,
        ':matricula' => $vehiculo->matricula
    ]);

    // Registrar historial con nombre legible del estado
    $nombreEstado = Vehiculo::obtenerNombreEstado($nuevoEstado);
    registrarHistorialVehiculo($pdo, $vehiculo->matricula, $dniTrabajador, "Cambio de estado a $nombreEstado", $descripcion);
}


function validarMatricula(PDO $pdo, $matricula, $existeEnDB = true)
{
    $matricula = strtoupper(str_replace(' ', '', $matricula));

    // Comprobar si ya existe
    if ($existeEnDB) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Vehiculo WHERE matricula = :matricula");
        $stmt->execute([':matricula' => $matricula]);
        if ($stmt->fetchColumn() > 0) {
            return "Matricula introducida ya esta registrada.";
        }
    }

    return true;
}

function obtenerFlotaFiltrada(array $get)
{
    $pdo = getPDO();
    $where = [];
    $params = [];

    if (!empty($get['estado'])) {
        $where[] = 'v.idEstado = :estado';
        $params['estado'] = $get['estado'];
    }

    if (!empty($get['marca'])) {
        $where[] = 'v.marca LIKE :marca';
        $params['marca'] = '%' . $get['marca'] . '%';
    }

    if (!empty($get['modelo'])) {
        $where[] = 'v.modelo LIKE :modelo';
        $params['modelo'] = '%' . $get['modelo'] . '%';
    }

    if (!empty($get['categoria'])) {
        $where[] = 'v.idCategoria = :categoria';
        $params['categoria'] = $get['categoria'];
    }

    if (!empty($get['km'])) {
        $where[] = 'v.kmActual <= :km';
        $params['km'] = $get['km'];
    }

    $sql = "
        SELECT 
            v.*,
            c.nombreCategoria,
            e.nombreEstado
        FROM Vehiculo v
        JOIN Categoria c ON v.idCategoria = c.idCategoria
        JOIN EstadoVehiculo e ON v.idEstado = e.idEstado
    ";

    if ($where) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($vehiculos as &$v) {
        // 1 LIMPIO 2 SUCIO
        $v['disponibilidad'] = in_array($v['idEstado'], [1, 2]);
    }

    return $vehiculos;
}


//Volver segun cual quier rol
/**
 *<a href="<?= volverSegunRol() ?>" class="nav-link">Volver</a> 
 */
function volverSegunRol(?string $rol = null): string
{
    if ($rol === null) {
        $rol = $_SESSION['usuario']['rol'] ?? null;
    }

    // Array de roles y sus paginas
    $dashboards = [
        'admin' => 'dashboardAdmin.php',
        'mecanico' => 'dashboardMecanico.php',
    ];


    if (isset($dashboards[$rol])) {
        return $dashboards[$rol];
    }

    // Rol desconocido 
    return 'index.php';
}
