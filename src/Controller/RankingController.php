<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RankingService;

class RankingController
{
    private RankingService $service;

    public function __construct()
    {
        $this->service = new RankingService();
    }

    public function ranking(): void
    {
        $movimento = isset($_GET['movimento']) ? trim($_GET['movimento']) : '';

        if ($movimento === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'O parâmetro "movimento" é obrigatório']);
            return;
        }

        $result = $this->service->getRanking($movimento);

        if (!$result['encontrado']) {
            http_response_code(404);
            echo json_encode(['erro' => $result['mensagem']]);
            return;
        }

        echo json_encode([
            'movimento' => $result['movimento'],
            'ranking'   => $result['ranking'],
        ]);
    }
}
