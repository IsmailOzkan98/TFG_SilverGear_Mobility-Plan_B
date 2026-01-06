<?php
require_once 'common.php';

class Vehiculo {
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
    public $fechaAdquisicion;
    public $contadorReservas;
    public $notasInternas;
    public $manipuladoPor;

    private $pdo;

    public function __construct($datos, PDO $pdo) {
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
        $this->disponibilidad = isset($datos['disponibilidad']) ? ($datos['disponibilidad'] ? 1 : 0) : 0;
        $this->kmInicial = $datos['kmInicial'] ?? 0;
        $this->kmActual = $datos['kmActual'] ?? 0;
        $this->fechaUltimaRevision = $datos['fechaUltimaRevision'] ?? null;
        $this->fechaProximaRevision = $datos['fechaProximaRevision'] ?? null;
        $this->imagenPrincipal = $datos['imagenPrincipal'] ?? null;
        $this->precioAdquisicion = $datos['precioAdquisicion'] ?? 0;
        $this->fechaAdquisicion = $datos['fechaAdquisicion'] ?? null;
        $this->contadorReservas = $datos['contadorReservas'] ?? 0;
        $this->notasInternas = $datos['notasInternas'] ?? '';
        $this->manipuladoPor = $datos['manipuladoPor'] ?? null;
    }

    // Guardar vehiculo
    public function guardar() {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Vehiculo 
                (matricula, marca, modelo, anio, color, numeroPlazas, tipoPropulsion, transmision, idCategoria, idEstado, plazaParking, disponibilidad, kmInicial, kmActual, fechaUltimaRevision, fechaProximaRevision, imagenPrincipal, precioAdquisicion, fechaAdquisicion, contadorReservas, notasInternas, manipuladoPor)
                VALUES
                (:matricula, :marca, :modelo, :anio, :color, :numeroPlazas, :tipoPropulsion, :transmision, :idCategoria, :idEstado, :plazaParking, :disponibilidad, :kmInicial, :kmActual, :fechaUltimaRevision, :fechaProximaRevision, :imagenPrincipal, :precioAdquisicion, :fechaAdquisicion, :contadorReservas, :notasInternas, :manipuladoPor)
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
                ':plazaParking' => $this->plazaParking,
                ':disponibilidad' => $this->disponibilidad,
                ':kmInicial' => $this->kmInicial,
                ':kmActual' => $this->kmActual,
                ':fechaUltimaRevision' => $this->fechaUltimaRevision,
                ':fechaProximaRevision' => $this->fechaProximaRevision,
                ':imagenPrincipal' => $this->imagenPrincipal,
                ':precioAdquisicion' => $this->precioAdquisicion,
                ':fechaAdquisicion' => $this->fechaAdquisicion,
                ':contadorReservas' => $this->contadorReservas,
                ':notasInternas' => $this->notasInternas,
                ':manipuladoPor' => $this->manipuladoPor
            ]);

            return ['exito' => true];

        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062 && str_contains($e->getMessage(), 'matricula')) {
                return ['errores' => ['matricula' => 'Matrícula ya registrada']];
            }
            return ['errores' => ['general' => $e->getMessage()]];
        }
    }

    
    public function esAlquilable(): bool {
        return in_array($this->idEstado, ['LIMPIO', 'SUCIO']) && $this->disponibilidad;
    }

    public function esVendible(): bool {
        return $this->idEstado === 'VENTAS' && $this->disponibilidad;
    }
}
