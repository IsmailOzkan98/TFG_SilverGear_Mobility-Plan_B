<?php


/* Obtener categoría completa*/
function obtenerCategoria(PDO $pdo, $idCategoria)
{
    $stmt = $pdo->prepare("SELECT * FROM Categoria WHERE idCategoria = ?");
    $stmt->execute([$idCategoria]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


/* Obtener nombre de categoría*/
function getNombreCategoria($idCategoria)
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT nombreCategoria FROM Categoria WHERE idCategoria = ?");
    $stmt->execute([$idCategoria]);
    return $stmt->fetchColumn();
}


/*Descuento según días*/
function obtenerDescuentoPorDias($categoria, $dias)
{
    if ($dias >= 20) return $categoria['descuentoDia20_mas'];
    if ($dias >= 11) return $categoria['descuentoDia11_19'];
    if ($dias >= 7)  return $categoria['descuentoDia7_10'];
    if ($dias >= 4)  return $categoria['descuentoDia4_6'];
    return $categoria['descuentoDia1_3'];
}


/*Precio diario alquiler*/
function calcularPrecioAlquiler($idCategoria, $dias, $aplicarSeguro = false, $recargoCarnetJoven = false)
{
    $pdo = getPDO();
    $categoria = obtenerCategoria($pdo, $idCategoria);
    if (!$categoria) return null;

    $precio = $categoria['precioBase'];

    if ($aplicarSeguro) $precio += $categoria['incrementoSeguro'];
    if ($recargoCarnetJoven) $precio += $categoria['recargoCarnetJoven'];

    $descuento = obtenerDescuentoPorDias($categoria, $dias);

    return round($precio * (1 - $descuento / 100), 2);
}

/*Precio total alquiler*/
function calcularPrecioTotal($idCategoria, $dias, $aplicarSeguro = false, $recargoCarnetJoven = false)
{
    $precioDia = calcularPrecioAlquiler($idCategoria, $dias, $aplicarSeguro, $recargoCarnetJoven);
    return $precioDia * $dias;
}


/* Crear categoria */
function crearCategoria(PDO $pdo, array $data): bool
{
    $sql = "
        INSERT INTO Categoria 
        (nombreCategoria, descripcion, incrementoSeguro, recargoCarnetJoven, precioBase,
         descuentoDia1_3, descuentoDia4_6, descuentoDia7_10, descuentoDia11_19, descuentoDia20_mas)
        VALUES 
        (:nombre, :descripcion, :seguro, :carnet, :precio,
         :d1_3, :d4_6, :d7_10, :d11_19, :d20)
    ";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':nombre' => $data['nombreCategoria'],
        ':descripcion' => $data['descripcion'] ?? null,
        ':seguro' => $data['incrementoSeguro'],
        ':carnet' => $data['recargoCarnetJoven'],
        ':precio' => $data['precioBase'],
        ':d1_3' => $data['descuentoDia1_3'],
        ':d4_6' => $data['descuentoDia4_6'],
        ':d7_10' => $data['descuentoDia7_10'],
        ':d11_19' => $data['descuentoDia11_19'],
        ':d20' => $data['descuentoDia20_mas']
    ]);
}

/* Editar categoria */
function editarCategoria(PDO $pdo, int $idCategoria, array $data): bool
{
    $sql = "
        UPDATE Categoria SET
            nombreCategoria = :nombre,
            descripcion = :descripcion,
            incrementoSeguro = :seguro,
            recargoCarnetJoven = :carnet,
            precioBase = :precio,
            descuentoDia1_3 = :d1_3,
            descuentoDia4_6 = :d4_6,
            descuentoDia7_10 = :d7_10,
            descuentoDia11_19 = :d11_19,
            descuentoDia20_mas = :d20
        WHERE idCategoria = :id
    ";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':id' => $idCategoria,
        ':nombre' => $data['nombreCategoria'],
        ':descripcion' => $data['descripcion'] ?? null,
        ':seguro' => $data['incrementoSeguro'],
        ':carnet' => $data['recargoCarnetJoven'],
        ':precio' => $data['precioBase'],
        ':d1_3' => $data['descuentoDia1_3'],
        ':d4_6' => $data['descuentoDia4_6'],
        ':d7_10' => $data['descuentoDia7_10'],
        ':d11_19' => $data['descuentoDia11_19'],
        ':d20' => $data['descuentoDia20_mas']
    ]);
}

/* bortrar categoria */
function eliminarCategoria(PDO $pdo, int $idCategoria): bool
{
    // esta en uso?
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Vehiculo WHERE idCategoria = ?");
    $stmt->execute([$idCategoria]);

    // si esta en uso bloqueo
    if ($stmt->fetchColumn() > 0) {
        return false; 
    }

    $stmt = $pdo->prepare("DELETE FROM Categoria WHERE idCategoria = ?");
    return $stmt->execute([$idCategoria]);
}