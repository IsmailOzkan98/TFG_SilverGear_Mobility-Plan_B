<?php
require_once 'common.php';
require_once 'security.php';
requireRole(['admin', 'cliente', 'ventas', 'mecanico', 'limpieza', 'dropoff']);

require_once '../vendor/autoload.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

if (empty($_SESSION['cesta'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Cesta vacía']);
    exit;
}

$pdo = getPDO();

$ids = implode(',', array_map('intval', array_keys($_SESSION['cesta'])));
$stmt = $pdo->query("
    SELECT marca, modelo, precioVenta
    FROM Vehiculo
    WHERE idVehiculo IN ($ids)
");

$vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$line_items = [];

foreach ($vehiculos as $v) {
    $line_items[] = [
        'price_data' => [
            'currency' => 'eur',
            'product_data' => [
                'name' => $v['marca'] . ' ' . $v['modelo'],
            ],
            'unit_amount' => (int)($v['precioVenta'] * 100),
        ],
        'quantity' => 1,
    ];
}

$session = \Stripe\Checkout\Session::create([
    'mode' => 'payment',
    'line_items' => $line_items,
    'success_url' => 'http://ismail.webserver.dtanase.com/pages/cesta.php?success=1',
    'cancel_url' => 'http://ismail.webserver.dtanase.com/pages/cesta.php?cancel=1',
]);

echo json_encode(['id' => $session->id]);
