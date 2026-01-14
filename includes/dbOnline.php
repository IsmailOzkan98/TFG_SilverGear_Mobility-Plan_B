<?php 
//he usado mismo archivo que para la practica ud3 pero con alguna modificacion minima

function conectar() {
    $host = 'ismail_mariadb';
    $dbName = 'gestion_vehiculos';
    $user = 'idruxUser';
    $pass = 'idruxPwd123';

    $charset = 'utf8mb4'; //sirve para soportar caracteres especiales
    $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset"; //indica a PDO que tipo de BD usar y como conectarse

    //aqui puse configuraciones que me facilitaron capturar fallos en conexion y en futuro poder añadir mas configuraciones comodamente
    $configuracion = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //no oculta mas los mensajes de fallos por lo cual puedes al menos saber porque no funcciona
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //hace que devuelva por defecto arrays asociativos
    ];

    //crear objeto PDO con datos para la conexion y tambien capturar fallos con try catch
    try{
        $pdo = new PDO($dsn, $user, $pass, $configuracion);
        return $pdo;
    } catch (PDOException $e) {
        echo "ERROR => " . $e -> getMessage();
        exit; //fuerza salida
    }


}