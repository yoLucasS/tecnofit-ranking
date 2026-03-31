<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controller\RankingController;

header('Content-Type: application/json; charset=utf-8');

$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// por enquanto só tem essa rota mesmo
if ($path === '/ranking' && $method === 'GET') {
    (new RankingController())->ranking();
} else {
    http_response_code(404);
    echo json_encode(['erro' => 'Rota não encontrada']);
}
