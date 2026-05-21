<?php
require_once 'common.php';

class Vehiculo
{
    public $matricula;
    public $marca;
    public $modelo;
    public $anio;
    public $color;
    public $numeroPlazas;
    public $tipoPropulsion;
    public $transmision;
    public $idCategoria;
    public $idEstado;
    public $plazaParking;
    public $disponibilidad;
    public $kmInicial;
    public $kmActual;
    public $fechaUltimaRevision;
    public $fechaProximaRevision;
    public $imagenPrincipal;
    public $precioAdquisicion;
    public $precioVenta;
    public $fechaAdquisicion;
    public $contadorReservas;
    public $notasInternas;
    public $manipuladoPor;

    private $pdo;

    private static $estadoNombres = [
        1 => 'LIMPIO',
        2 => 'SUCIO',
        3 => 'IMPRO',
        4 => 'VENTAS',
        5 => 'BAJA',
        6 => 'VENDIDO',
        7 => 'ALQUILADO'
    ];

    public function __construct($datos, PDO $pdo)
    {
        $this->pdo = $pdo;

        $this->matricula = $datos['matricula'] ?? '';
        $this->marca = $datos['marca'] ?? '';
        $this->modelo = $datos['modelo'] ?? '';
        $this->anio = $datos['anio'] ?? '';
        $this->color = $datos['color'] ?? '';
        $this->numeroPlazas = $datos['numeroPlazas'] ?? 0;
        $this->tipoPropulsion = $datos['tipoPropulsion'] ?? '';
        $this->transmision = $datos['transmision'] ?? '';
        $this->idCategoria = $datos['idCategoria'] ?? null;
        $this->idEstado = $datos['idEstado'] ?? null;
        $this->plazaParking = $datos['plazaParking'] ?? null;
        $this->kmInicial = $datos['kmInicial'] ?? 0;
        $this->kmActual = $datos['kmActual'] ?? 0;
        $this->fechaUltimaRevision = $datos['fechaUltimaRevision'] ?? null;
        $this->fechaProximaRevision = $datos['fechaProximaRevision'] ?? null;
        $this->imagenPrincipal = $datos['imagenPrincipal'] ?? null;
        $this->precioAdquisicion = $datos['precioAdquisicion'] ?? 0;
        $this->precioVenta = $datos['precioVenta'] ?? null;
        $this->fechaAdquisicion = $datos['fechaAdquisicion'] ?? null;
        $this->contadorReservas = $datos['contadorReservas'] ?? 0;
        $this->notasInternas = $datos['notasInternas'] ?? '';
        $this->manipuladoPor = $datos['manipuladoPor'] ?? null;

        $this->actualizarDisponibilidad();
    }

    public function actualizarDisponibilidad()
    {
        $nombre = self::$estadoNombres[$this->idEstado] ?? '';
        $this->disponibilidad = in_array($nombre, ['LIMPIO', 'SUCIO', 'IMPRO', 'VENTAS', 'BAJA', 'VENDIDO', 'ALQUILADO']);
    }

    public function esAlquilable(): bool
    {
        return in_array(self::$estadoNombres[$this->idEstado] ?? '', ['LIMPIO', 'SUCIO']);
    }

    public function esVendible(): bool
    {
        return (self::$estadoNombres[$this->idEstado] ?? '') === 'VENTAS';
    }

    public function guardar()
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Vehiculo SET 
                    marca=:marca, modelo=:modelo, anio=:anio, color=:color,
                    numeroPlazas=:numeroPlazas, tipoPropulsion=:tipoPropulsion, transmision=:transmision,
                    idCategoria=:idCategoria, idEstado=:idEstado, disponibilidad=:disponibilidad,
                    kmActual=:kmActual, fechaUltimaRevision=:fechaUltimaRevision, fechaProximaRevision=:fechaProximaRevision,
                    precioAdquisicion=:precioAdquisicion, fechaAdquisicion=:fechaAdquisicion, notasInternas=:notasInternas,
                    manipuladoPor=:manipuladoPor
                WHERE matricula=:matricula
            ");

            $stmt->execute([
                ':matricula' => $this->matricula,
                ':marca' => $this->marca,
                ':modelo' => $this->modelo,
                ':anio' => $this->anio,
                ':color' => $this->color,
                ':numeroPlazas' => $this->numeroPlazas,
                ':tipoPropulsion' => $this->tipoPropulsion,
                ':transmision' => $this->transmision,
                ':idCategoria' => $this->idCategoria,
                ':idEstado' => $this->idEstado,
                ':disponibilidad' => $this->disponibilidad,
                ':kmActual' => $this->kmActual,
                ':fechaUltimaRevision' => $this->fechaUltimaRevision,
                ':fechaProximaRevision' => $this->fechaProximaRevision,
                ':precioAdquisicion' => $this->precioAdquisicion,
                ':fechaAdquisicion' => $this->fechaAdquisicion,
                ':notasInternas' => $this->notasInternas,
                ':manipuladoPor' => $this->manipuladoPor
            ]);

            return ['exito' => true];
        } catch (PDOException $e) {
            return ['errores' => ['general' => $e->getMessage()]];
        }
    }

    
    public function guardarNuevo()
    {
        try {
            $stmt = $this->pdo->prepare("
            INSERT INTO Vehiculo (
                matricula, marca, modelo, anio, color,
                numeroPlazas, tipoPropulsion, transmision,
                idCategoria, idEstado, disponibilidad,
                kmInicial, kmActual, fechaUltimaRevision, fechaProximaRevision,
                precioAdquisicion, fechaAdquisicion, notasInternas, manipuladoPor
            ) VALUES (
                :matricula, :marca, :modelo, :anio, :color,
                :numeroPlazas, :tipoPropulsion, :transmision,
                :idCategoria, :idEstado, :disponibilidad,
                :kmInicial, :kmActual, :fechaUltimaRevision, :fechaProximaRevision,
                :precioAdquisicion, :fechaAdquisicion, :notasInternas, :manipuladoPor
            )
        ");

            $stmt->execute([
                ':matricula' => $this->matricula,
                ':marca' => $this->marca,
                ':modelo' => $this->modelo,
                ':anio' => $this->anio,
                ':color' => $this->color,
                ':numeroPlazas' => $this->numeroPlazas,
                ':tipoPropulsion' => $this->tipoPropulsion,
                ':transmision' => $this->transmision,
                ':idCategoria' => $this->idCategoria,
                ':idEstado' => $this->idEstado,
                ':disponibilidad' => $this->disponibilidad ?? 1,
                ':kmInicial' => $this->kmInicial,
                ':kmActual' => $this->kmActual,
                ':fechaUltimaRevision' => $this->fechaUltimaRevision,
                ':fechaProximaRevision' => $this->fechaProximaRevision,
                ':precioAdquisicion' => $this->precioAdquisicion,
                ':fechaAdquisicion' => $this->fechaAdquisicion,
                ':notasInternas' => $this->notasInternas,
                ':manipuladoPor' => $this->manipuladoPor
            ]);

            return ['exito' => true];
        } catch (PDOException $e) {
            return ['errores' => ['general' => $e->getMessage()]];
        }
    }



    public static function obtenerNombreEstado($idEstado)
    {
        return self::$estadoNombres[$idEstado] ?? '';
    }
}
