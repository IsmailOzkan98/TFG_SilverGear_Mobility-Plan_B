<?php
require_once 'includes/common.php';

$pdo = getPDO();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ==============================
// Datos base
// ==============================
$dniSistema = 'ADMIN000';

$idEstadoSucio = $pdo->query("
    SELECT idEstado 
    FROM EstadoVehiculo 
    WHERE nombreEstado = 'SUCIO'
")->fetchColumn();

if (!$idEstadoSucio) {
    die('❌ Estado SUCIO no existe');
}

// ==============================
// Generador matrícula
// ==============================
function generarMatricula($i) {
    $num = str_pad(1000 + $i, 4, '0', STR_PAD_LEFT);
    $letras =
        chr(65 + ($i % 26)) .
        chr(65 + (($i + 5) % 26)) .
        chr(65 + (($i + 10) % 26));
    return $num . '-' . $letras;
}

// ==============================
// Flota
// ==============================
$flota = [
    ['Fiat','Panda','Gasolina','Manual',4,1,3],
    ['Toyota','Aygo','Gasolina','Manual',4,1,3],
    ['Dacia','Spring','Eléctrico','Automático',4,1,2],

    ['Seat','León','Gasolina','Manual',5,2,3],
    ['Volkswagen','Golf','Diesel','Manual',5,2,3],
    ['Toyota','Corolla','Híbrido','Automático',5,2,2],

    ['Skoda','Octavia','Diesel','Manual',5,3,3],
    ['Mazda','6','Gasolina','Manual',5,3,2],
    ['Tesla','Model 3','Eléctrico','Automático',5,3,2],

    ['Nissan','Qashqai','Gasolina','Manual',5,4,3],
    ['Hyundai','Tucson','Híbrido','Automático',5,4,2],
    ['Toyota','RAV4','Híbrido','Automático',5,4,2],

    ['BMW','Serie 5','Diesel','Automático',5,5,2],
    ['Audi','A6','Diesel','Automático',5,5,2],
    ['Mercedes','Clase E','Gasolina','Automático',5,5,2],

    ['Volkswagen','Caravelle','Diesel','Manual',9,6,3],
    ['Ford','Tourneo Custom','Diesel','Manual',9,6,2],

    ['Renault','Kangoo Cargo','Diesel','Manual',2,7,3],
    ['Mercedes','Citan','Diesel','Manual',2,7,2],

    ['Volkswagen','Beetle','Gasolina','Manual',4,8,2,1972],
    ['Mini','Cooper Classic','Gasolina','Manual',4,8,2,1968],
];

// ==============================
// SQL directa (sin clase)
// ==============================
$sql = "
INSERT INTO Vehiculo (
    matricula,
    marca,
    modelo,
    anio,
    color,
    numeroPlazas,
    tipoPropulsion,
    transmision,
    idCategoria,
    idEstado,
    plazaParking,
    disponibilidad,
    kmInicial,
    kmActual,
    fechaUltimaRevision,
    fechaProximaRevision,
    imagenPrincipal,
    precioAdquisicion,
    fechaAdquisicion,
    contadorReservas,
    notasInternas,
    manipuladoPor
) VALUES (
    :matricula,
    :marca,
    :modelo,
    :anio,
    :color,
    :plazas,
    :propulsion,
    :transmision,
    :idCategoria,
    :idEstado,
    :plazaParking,
    :disponibilidad,
    :kmInicial,
    :kmActual,
    :fechaUltimaRevision,
    :fechaProximaRevision,
    :imagenPrincipal,
    :precioAdquisicion,
    :fechaAdquisicion,
    :contadorReservas,
    :notasInternas,
    :manipuladoPor
)";
$stmt = $pdo->prepare($sql);

// ==============================
// Inserción
// ==============================
$contador = 1;
$insertados = 0;

foreach ($flota as $item) {

    [
        $marca,
        $modelo,
        $propulsion,
        $transmision,
        $plazas,
        $idCategoria,
        $unidades,
        $anioEspecial
    ] = array_pad($item, 8, null);

    for ($i = 0; $i < $unidades; $i++) {

        $anio = $anioEspecial ?? 2024;

        $km = ($anio < 2000)
            ? rand(120000, 220000)
            : rand(5000, 25000);

        $stmt->execute([
            ':matricula' => generarMatricula($contador),
            ':marca' => strtoupper($marca),
            ':modelo' => strtoupper($modelo),
            ':anio' => $anio,
            ':color' => 'BLANCO',
            ':plazas' => $plazas,
            ':propulsion' => $propulsion,
            ':transmision' => $transmision,
            ':idCategoria' => $idCategoria,
            ':idEstado' => $idEstadoSucio,
            ':plazaParking' => 0,
            ':disponibilidad' => 1,
            ':kmInicial' => $km,
            ':kmActual' => $km,
            ':fechaUltimaRevision' => date('Y-m-d', strtotime('-6 months')),
            ':fechaProximaRevision' => date('Y-m-d', strtotime('+6 months')),
            ':imagenPrincipal' => null,
            ':precioAdquisicion' => null,
            ':fechaAdquisicion' => date('Y-m-d', strtotime('-1 year')),
            ':contadorReservas' => 0,
            ':notasInternas' => 'Alta inicial automática',
            ':manipuladoPor' => $dniSistema
        ]);

        $contador++;
        $insertados++;
    }
}

echo "✔ Vehículos insertados correctamente: $insertados";
