<?php
require_once 'common.php';

class Usuario {
    public $nombre;
    public $apellidos;
    public $dni;
    public $fechaNacimiento;
    public $sexo;
    public $direccion;
    public $ciudad;
    public $pais;
    public $codigoPostal;
    public $telefono;
    public $email;
    public $fechaCarnet;
    private $contrasenaHash;
    public $idRol;

    private $pdo;

    public function __construct($datos, PDO $pdo) {
        $this->pdo = $pdo;

        $this->nombre = $datos['nombre'] ?? '';
        $this->apellidos = $datos['apellidos'] ?? '';
        $this->dni = $datos['dni'] ?? '';
        $this->fechaNacimiento = $datos['fechaNacimiento'] ?? '';
        $this->sexo = $datos['sexo'] ?? null;
        $this->direccion = $datos['direccion'] ?? '';
        $this->ciudad = $datos['ciudad'] ?? '';
        $this->pais = $datos['pais'] ?? '';
        $this->codigoPostal = $datos['codigoPostal'] ?? null;
        $this->telefono = $datos['telefono'] ?? '';
        $this->email = $datos['email'] ?? '';
        $this->fechaCarnet = $datos['fechaCarnet'] ?? '';

        $this->idRol = 2; // rol cliente por defecto

        // Contraseña
        $this->setContrasena($datos['contrasena'] ?? '', $datos['repetirContrasena'] ?? '');
    }

    private function setContrasena($pass, $repetir) {
        // Validar contraseñas con funciones de common.php
        $error = validarContrasena($pass);
        if ($error !== true) throw new Exception($error);

        $errorRepetir = validarContrasenaRepetida($pass, $repetir);
        if ($errorRepetir !== true) throw new Exception($errorRepetir);

        // Generar hash
        $this->contrasenaHash = hashContrasena($pass);
    }

    public function validar() {
        $errores = [];

        $campos = [
            'nombre' => validarNombre($this->nombre),
            'apellidos' => validarApellidos($this->apellidos),
            'dni' => validarDNI($this->pdo, $this->dni),  
            'fechaNacimiento' => validarFecha($this->fechaNacimiento),
            'direccion' => validarTexto($this->direccion, 'Dirección'),
            'ciudad' => validarTexto($this->ciudad, 'Ciudad'),
            'pais' => validarTexto($this->pais, 'País'),
            'codigoPostal' => validarCodigoPostal($this->codigoPostal),
            'telefono' => validarTelefono($this->telefono),
            'email' => validarEmail($this->email, $this->pdo),
            'fechaCarnet' => validarFecha($this->fechaCarnet),
        ];

        foreach ($campos as $campo => $resultado) {
        if ($resultado !== true) {
            $errores[$campo] = $resultado;
        }
    }

        return $errores;
    }

    public function guardar() {
        $errores = $this->validar();
        if (!empty($errores)) return ['errores' => $errores];

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Usuario 
                (nombre, apellidos, dni, fechaNacimiento, sexo, direccion, ciudad, pais, codigoPostal, telefono, email, fechaCarnet, contrasena, idRol)
                VALUES 
                (:nombre, :apellidos, :dni, :fechaNacimiento, :sexo, :direccion, :ciudad, :pais, :codigoPostal, :telefono, :email, :fechaCarnet, :contrasena, :idRol)
            ");

            $stmt->execute([
                ':nombre' => $this->nombre,
                ':apellidos' => $this->apellidos,
                ':dni' => $this->dni,
                ':fechaNacimiento' => $this->fechaNacimiento,
                ':sexo' => $this->sexo,
                ':direccion' => $this->direccion,
                ':ciudad' => $this->ciudad,
                ':pais' => $this->pais,
                ':codigoPostal' => $this->codigoPostal,
                ':telefono' => $this->telefono,
                ':email' => $this->email,
                ':fechaCarnet' => $this->fechaCarnet,
                ':contrasena' => $this->contrasenaHash,
                ':idRol' => $this->idRol
            ]);

            return ['exito' => true];

        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { 
                if (str_contains($e->getMessage(), 'dni')) return ['errores' => ['dni' => 'DNI ya registrado']];
                if (str_contains($e->getMessage(), 'email')) return ['errores' => ['email' => 'Email ya registrado']];
            }
            return ['errores' => ['general' => $e->getMessage()]];
        }
    }
}
