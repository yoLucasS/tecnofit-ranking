<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\RankingRepository;

class RankingService
{
    private RankingRepository $repo;

    public function __construct(?RankingRepository $repo = null)
    {
        $this->repo = $repo ?? new RankingRepository();
    }

    public function getRanking(string $identificador): array
    {
        $movimento = $this->repo->buscarMovimento($identificador);

        if ($movimento === null) {
            return [
                'encontrado' => false,
                'mensagem'   => 'Movimento não encontrado',
            ];
        }

        $linhas = $this->repo->getRankingPorMovimento($movimento['id']);

        $ranking = [];
        foreach ($linhas as $l) {
            $ranking[] = [
                'nome'            => $l['nome_usuario'],
                'recorde_pessoal' => (float) $l['recorde_pessoal'],
                'posicao'         => (int) $l['posicao'],
                'data_recorde'    => $l['data_recorde'],
            ];
        }

        return [
            'encontrado' => true,
            'movimento'  => $movimento['name'],
            'ranking'    => $ranking,
        ];
    }
}
