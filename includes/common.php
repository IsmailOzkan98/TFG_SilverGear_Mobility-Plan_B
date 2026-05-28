<?php
require_once 'db.php';
require_once __DIR__ . '/categorias.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//Open WeatherMap
require_once __DIR__ . "/../weather.php";
$weather = getWeather();

//Stripe
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../apikeys.php';



function getPDO()
{
    static $pdo = null; //conexion una vez
    if ($pdo === null) {
        $pdo = conectar();
    }
    return $pdo;
}


//favicon
function imprimirFavicon(string $ruta = '../images/favicon/favicon.ico'): void
{
    echo '<link rel="icon" href="' . htmlspecialchars($ruta) . '" type="image/x-icon">';
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




function validarDNI(PDO $pdo, $dni, $existeEnDB = true, $dniActual = null)
{
    $dni = strtoupper(str_replace([' ', '-'], '', $dni));

    // validar formato
    if (!preg_match("/^[0-9]{8}[A-Z]$/", $dni) && !preg_match("/^[XYZ][0-9]{7,8}[A-Z]$/", $dni)) {
        return "Formato de DNI o NIE introducido no es adecuado";
    }


    // Comprobar su existencia en bd
    if ($existeEnDB) {

        $resultado = "SELECT COUNT(*) FROM Usuario WHERE dni = :dni";


        //exclulle dni actual de la busqueda
        if ($dniActual !== null) {
            $resultado .= " AND dni != :dniActual";
        }

        $stmt = $pdo->prepare($resultado);

        $params = [':dni' => $dni];

        if ($dniActual !== null) {
            $params[':dniActual'] = $dniActual;
        }

        $stmt->execute($params);


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

function validarTexto($texto, $campo, $obligatorio = false) //ciudad, pais, direccion, marca y modelo
{
    if ($texto === null || $texto === '') {
        if ($obligatorio) {
            return "$campo es obligatorio.";
        }

        return true; //opcional
    }

    if (!is_string($texto)) {
        return "$campo debe ser texto.";
    }

    $texto = trim($texto);

    return true;
}

function validarCodigoPostal($cp)
{
    if ($cp === null || $cp === '') return true; // opcional

    $cp = trim($cp);

    if (!preg_match("/^\d{5}$/", $cp)) return "Codigo postal es invalido.";
    return true;
}

function validarTelefono($telefono)
{
    if (empty($telefono)) return "El teléfono es obligatorio!";



    if (!preg_match('/^[0-9\s-]+$/', $telefono)) { // Acepta 9 digitos seguidos, o mezclarlo con espacio o guion
        return "Telefono introducido contiene caracteres invalidos";
    }

    $numero = str_replace([' ', '-'], '', $telefono); // quita espacios y guiones

    if (!preg_match('/^\d{9}$/', $numero)) { //revisa que telefono tenga exactamente 9 numeros
        return "Telefono introducido no contiene 9 numeros";
    }


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



// Validar email Con verificacion de si existe ya
function validarEmail($email, PDO $pdo, $existeEnDB = true, $dniActual = null)
{
    if (empty($email)) return "El email es obligatorio!";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Formato de email introducido no es valido";
    }


    // Comprobar si ya existe mediante dni
    if ($existeEnDB) {

        $resultado = "SELECT COUNT(*) FROM Usuario WHERE email = :email"; //consulta para conseguir datos del usuario al que pertenece ese email
        $params = [':email' => $email];

        if ($dniActual !== null) {
            $resultado .= " AND dni != :dni";
            $params[':dni'] = $dniActual;
        }

        $stmt = $pdo->prepare($resultado);
        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            return "El email ya esta registrado.";
        }
    }

    return true;
}


//Valicaciones de Vehiculos
function validarColor($color){
    $color = trim($color ?? '');

    if ($color === '') {
        return true;
    } //opcional

    $color = strtoupper($color);

    if (!preg_match("/^[A-ZÁÉÍÓÚÑ ]+$/u", $color)) {
        return "El color solo puede contener letras.";
    }

    return true;
}

function validarFechaNoFutura($fecha, $campo)
{
    if (empty($fecha)) {
        return "$campo es obligatorio!";
    }

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)) {
        return "$campo formato no es adecuado";
    }

    if (strtotime($fecha) > time()) {
        return "$campo no debe ser futura.";
    }

    return true;
}

function validarPlazas($plazas)
{
    if ($plazas === null || $plazas === '') {
        return "Indicar numero plazas es obligatorio";
    }

    $plazas = (int)$plazas;

    if ($plazas < 2 || $plazas > 9) {
        return "Las plazas deben estar entre 2 y 9.";
    }

    return true;
}

function validarNoNegativo($valor, $campo)
{
    if ($valor === null || $valor === '') return "$campo es obligatorio.";

    if (!is_numeric($valor) || $valor < 0) {
        return "$campo no puede ser negativo.";
    }

    return true;
}

function validarMatricula(PDO $pdo, $matricula, $existeEnDB = true, $actual = null)
{

    if (empty($matricula)) {
        return "Matricula es obligatoria!";
    }

    $matricula = strtoupper(str_replace([' ', '-'], '', trim($matricula)));

    $patrones = [
        "/^[A-Z]\d{4}[A-Z]{4}$/", // A1234ABCD tipo historia
        "/^[A-Z]\d{4}[A-Z]{2}$/", // A1234AB antigua
        "/^\d{4}[A-Z]{4}$/"       // 1234ABCD moderna
    ];

    //validacion
    $validado = false;
    foreach ($patrones as $p) {
        if (preg_match($p, $matricula)) {
            $validado = true;
            break;
        }
    }

    if (!$validado) {
        return " introducida no es adecuada";
    }

    //comprobacion en bd
    if ($existeEnDB) {
        $resultado = "SELECT COUNT(*) FROM Vehiculo WHERE matricula = :matricula";
        if ($actual !== null) $resultado .= " AND matricula != :actual";

        $stmt = $pdo->prepare($resultado);
        $params = [':matricula' => $matricula];

        if ($actual !== null) $params[':actual'] = $actual;

        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            return " ya esta registrada";
        }
    }

    return true;
}

function validarAnio($anio)
{
    if (empty($anio)) {
        return "Año es obligatorio.";
    }

    $anio = (int)$anio;
    $actual = (int)date('Y');

    if ($anio > $actual){
        return "El año no puede ser futuro.";
    } 

    return true;
}

//VEHICULO Gestion

function actualizarDisponibilidadVehiculo(&$vehiculo)
{
    switch ($vehiculo->idEstado) {
        case 3: // IMPRO
        case 5: // BAJA
        case 6: // VENDIDO
        case 7: // ALQUILADO
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

    $dashboards = [
        'admin'     => '../dashboard/dashboardAdmin.php',
        'ventas'    => '../dashboard/dashboardVentas.php',
        'limpieza'  => '../dashboard/dashboardLimpieza.php',
        'dropoff'   => '../dashboard/dashboardDropoff.php',
        'mecanico'  => '../dashboard/dashboardMecanico.php',
        'cliente'  => '../pages/miPerfil.php',
    ];

    return $dashboards[$rol] ?? 'index.php';
}

//redirige segun rol principalmente pensado para login.php
function redirigirSegunRol(?string $rol = null): void
{
    if ($rol === null) {
        $rol = $_SESSION['usuario']['rol'] ?? null;
    }

    $dashboards = [
        'admin'     => '../dashboard/dashboardAdmin.php',
        'ventas'    => '../dashboard/dashboardVentas.php',
        'limpieza'  => '../dashboard/dashboardLimpieza.php',
        'dropoff'   => '../dashboard/dashboardDropoff.php',
        'mecanico'  => '../dashboard/dashboardMecanico.php',
        'cliente'   => 'tiendaComprar.php',
    ];

    header('Location: ' . ($dashboards[$rol] ?? '../index.php'));
    exit;
}



//comprobar el retraso de entrega
function comprobarRetrasoEntrega(string $fechaFin): ?int
{
    $hoy = new DateTime('today');
    $fin = new DateTime($fechaFin);

    if ($hoy <= $fin) {
        return null;
    }

    return $fin->diff($hoy)->days;
}


//Parches de compatibilidad
function normalizarValor($valor)
{
    $valor = trim(mb_strtoupper($valor, 'UTF-8'));

    $buscar = ['Á','À','Ä','Â','É','È','Ë','Ê','Í','Ì','Ï','Î','Ó','Ò','Ö','Ô','Ú','Ù','Ü','Û','Ñ'];
    $reemplazar = ['A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','U','U','U','U','N'];

    return str_replace($buscar, $reemplazar, $valor);
}

function normalizarSeleccion(array $opciones, $valorActual)
{
    foreach ($opciones as $opcion) {
        if (normalizarValor($opcion) === normalizarValor($valorActual)) {
            return $opcion;
        }
    }

    return null;
}

function coincidirOpcion(array $opciones, $valorBD)
{
    $valorNormalizado = normalizarValor($valorBD);

    foreach ($opciones as $opcion) {
        if (normalizarValor($opcion) === $valorNormalizado) {
            return $opcion; 
        }
    }

    return null;
}