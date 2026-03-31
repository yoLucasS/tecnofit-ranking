<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use PDO;

class RankingRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function getRankingPorMovimento(int $movimentoId): array
    {
        $sql = "
            SELECT
                u.name   AS nome_usuario,
                pr.value AS recorde_pessoal,
                pr.date  AS data_recorde,
                RANK() OVER (ORDER BY pr.value DESC) AS posicao
            FROM personal_record pr
            INNER JOIN user u ON u.id = pr.user_id
            WHERE pr.movement_id = :mid
              AND pr.value = (
                  SELECT MAX(pr2.value)
                  FROM personal_record pr2
                  WHERE pr2.user_id = pr.user_id
                    AND pr2.movement_id = pr.movement_id
              )
            ORDER BY pr.value DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mid' => $movimentoId]);

        return $stmt->fetchAll();
    }

    public function buscarMovimento(string $identificador): ?array
    {
        if (ctype_digit($identificador)) {
            $sql = 'SELECT id, name FROM movement WHERE id = :val LIMIT 1';
        } else {
            $sql = 'SELECT id, name FROM movement WHERE name = :val LIMIT 1';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':val' => $identificador]);
        $row = $stmt->fetch();

        return $row ?: null;
    }
}
