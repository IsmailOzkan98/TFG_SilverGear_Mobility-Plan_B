<?php
require_once 'common.php';
require_once 'security.php';
requireRole(['admin', 'ventas', 'mecanico']);

require_once '../vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$pdo = getPDO();
$usuarioSesion = $_SESSION['usuario'];
$dniUsuario = $usuarioSesion['dni'] ?? null;

$idPenalizacion = $_GET['id'] ?? null;

if (!$idPenalizacion) {
    http_response_code(400);
    echo json_encode(['error' => 'Penalización no especificada']);
    exit;
}

// Obtener penalización pendiente
$stmt = $pdo->prepare("
    SELECT idPenalizacion, cantidad, nota
    FROM Penalizacion
    WHERE idPenalizacion = :id AND dniCliente = :dni AND estadoPenalizacion = 'PENDIENTE'
");
$stmt->execute([':id' => $idPenalizacion, ':dni' => $dniUsuario]);
$penalizacion = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$penalizacion) {
    http_response_code(400);
    echo json_encode(['error' => 'Penalización no encontrada o ya pagada']);
    exit;
}

// Crear sesión de Stripe
$line_items = [[
    'price_data' => [
        'currency' => 'eur',
        'product_data' => [
            'name' => 'Penalización #' . $penalizacion['idPenalizacion'] . ' — ' . $penalizacion['nota'],
        ],
        'unit_amount' => (int)($penalizacion['cantidad'] * 100),
    ],
    'quantity' => 1
]];

$session = \Stripe\Checkout\Session::create([
    'mode' => 'payment',
    'line_items' => $line_items,
    'success_url' => 'http://localhost:8080/pages/penalizacion.php?success=1&id=' . $idPenalizacion,
    'cancel_url' => 'http://localhost:8080/pages/penalizacion.php?cancel=1',
]);

echo json_encode(['id' => $session->id]);
