<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $pdo = null;
    public static function getInstance(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'tecnofit';
        $user = getenv('DB_USER') ?: 'tecnofit';
        $pass = getenv('DB_PASS') ?: '';

        try {
            self::$pdo = new PDO(
                "mysql:host={$host};dbname={$db};charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Erro de conexão: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['erro' => 'Erro interno do servidor']);
            exit;
        }

        return self::$pdo;
    }

    private function __construct() {}
    private function __clone() {}
}
