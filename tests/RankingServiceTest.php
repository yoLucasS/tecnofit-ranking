<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Repository\RankingRepository;
use App\Service\RankingService;

final class FakeRankingRepository extends RankingRepository
{
    public ?string $ultimoIdentificador = null;
    public ?int $ultimoMovimentoId = null;
    private ?array $movimento;
    private array $linhas;

    public function __construct(?array $movimento, array $linhas = [])
    {
        $this->movimento = $movimento;
        $this->linhas = $linhas;
    }

    public function buscarMovimento(string $identificador): ?array
    {
        $this->ultimoIdentificador = $identificador;

        return $this->movimento;
    }

    public function getRankingPorMovimento(int $movimentoId): array
    {
        $this->ultimoMovimentoId = $movimentoId;

        return $this->linhas;
    }
}

function assertSameValue(mixed $esperado, mixed $atual, string $mensagem): void
{
    if ($esperado !== $atual) {
        throw new RuntimeException(
            $mensagem . PHP_EOL .
            'Esperado: ' . var_export($esperado, true) . PHP_EOL .
            'Atual: ' . var_export($atual, true)
        );
    }
}

function assertArrayHasKeyValue(string $chave, array $array, string $mensagem): void
{
    if (!array_key_exists($chave, $array)) {
        throw new RuntimeException($mensagem . PHP_EOL . "Chave ausente: {$chave}");
    }
}

$tests = [
    'retorna erro quando movimento nao existe' => function (): void {
        $repo = new FakeRankingRepository(null);
        $service = new RankingService($repo);

        $resultado = $service->getRanking('Movimento Inexistente');

        assertSameValue(false, $resultado['encontrado'], 'Movimento inexistente deve retornar encontrado=false.');
        assertSameValue("Movimento n\u{00E3}o encontrado", $resultado['mensagem'], 'Mensagem de movimento inexistente incorreta.');
    },
    'consulta movimento pelo identificador recebido' => function (): void {
        $repo = new FakeRankingRepository(['id' => 7, 'name' => 'Deadlift']);
        $service = new RankingService($repo);

        $service->getRanking('Deadlift');

        assertSameValue('Deadlift', $repo->ultimoIdentificador, 'Servico deve repassar o identificador ao repositorio.');
    },
    'usa id do movimento encontrado para buscar ranking' => function (): void {
        $repo = new FakeRankingRepository(['id' => 3, 'name' => 'Back Squat']);
        $service = new RankingService($repo);

        $service->getRanking('Back Squat');

        assertSameValue(3, $repo->ultimoMovimentoId, 'Servico deve buscar ranking pelo id do movimento encontrado.');
    },
    'retorna nome do movimento e ranking' => function (): void {
        $repo = new FakeRankingRepository(
            ['id' => 1, 'name' => 'Deadlift'],
            [[
                'nome_usuario' => 'Joao',
                'recorde_pessoal' => '180.00',
                'posicao' => '1',
                'data_recorde' => '2021-01-02 00:00:00',
            ]]
        );
        $service = new RankingService($repo);

        $resultado = $service->getRanking('1');

        assertSameValue(true, $resultado['encontrado'], 'Movimento encontrado deve retornar encontrado=true.');
        assertSameValue('Deadlift', $resultado['movimento'], 'Nome do movimento retornado incorreto.');
        assertSameValue('Joao', $resultado['ranking'][0]['nome'], 'Nome do usuario no ranking incorreto.');
    },
    'converte recorde e posicao para tipos corretos' => function (): void {
        $repo = new FakeRankingRepository(
            ['id' => 2, 'name' => 'Bench Press'],
            [[
                'nome_usuario' => 'Maria',
                'recorde_pessoal' => '95.5',
                'posicao' => '2',
                'data_recorde' => '2021-02-10 00:00:00',
            ]]
        );
        $service = new RankingService($repo);

        $ranking = $service->getRanking('Bench Press')['ranking'][0];

        assertSameValue(95.5, $ranking['recorde_pessoal'], 'Recorde pessoal deve ser convertido para float.');
        assertSameValue(2, $ranking['posicao'], 'Posicao deve ser convertida para int.');
    },
    'mantem data do recorde no item do ranking' => function (): void {
        $repo = new FakeRankingRepository(
            ['id' => 4, 'name' => 'Clean'],
            [[
                'nome_usuario' => 'Ana',
                'recorde_pessoal' => '120',
                'posicao' => '1',
                'data_recorde' => '2021-03-15 00:00:00',
            ]]
        );
        $service = new RankingService($repo);

        $ranking = $service->getRanking('Clean')['ranking'][0];

        assertArrayHasKeyValue('data_recorde', $ranking, 'Ranking deve conter a data do recorde.');
        assertSameValue('2021-03-15 00:00:00', $ranking['data_recorde'], 'Data do recorde incorreta.');
    },
];

$falhas = 0;

foreach ($tests as $nome => $test) {
    try {
        $test();
        echo "[OK] {$nome}" . PHP_EOL;
    } catch (Throwable $e) {
        $falhas++;
        echo "[FALHA] {$nome}" . PHP_EOL;
        echo $e->getMessage() . PHP_EOL;
    }
}

if ($falhas > 0) {
    echo PHP_EOL . "{$falhas} teste(s) falharam." . PHP_EOL;
    exit(1);
}

echo PHP_EOL . count($tests) . " teste(s) executados com sucesso." . PHP_EOL;
