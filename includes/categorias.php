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