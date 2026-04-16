# Tecnofit Ranking API

API simples pra retornar o ranking de movimentos (Deadlift, Back Squat, etc) baseado nos recordes pessoais dos usuários. PHP 8.2, sem framework, MySQL 8.

## Como rodar

Tem duas formas: via XAMPP ou via Docker.

### Opção 1 — XAMPP

1. Joga a pasta `tecnofit-ranking` dentro do `htdocs`:
```
C:\xampp\htdocs\tecnofit-ranking\
```

2. Abre o phpMyAdmin (`http://localhost/phpmyadmin`), cria um banco `tecnofit` e roda o `init.sql` nele.

3. Instala as dependências:
```bash
cd C:\xampp\htdocs\tecnofit-ranking
composer install
```

4. Testa:
```
http://localhost/tecnofit-ranking/public/ranking?movimento=Deadlift
```

### Opção 2 — Docker

```bash
docker compose up --build
```

Daí acessa `http://localhost:8080/ranking?movimento=Deadlift`

## Endpoint

```
GET /ranking?movimento={nome ou id}
```

O parâmetro `movimento` pode ser o nome do movimento (ex: `Deadlift`, `Back Squat`) ou o ID numérico dele.

### Respostas

Sucesso (200):
```json
{
  "movimento": "Deadlift",
  "ranking": [
    { "nome": "Jose",  "recorde_pessoal": 190, "posicao": 1, "data_recorde": "2021-01-06 00:00:00" },
    { "nome": "Joao",  "recorde_pessoal": 180, "posicao": 2, "data_recorde": "2021-01-02 00:00:00" },
    { "nome": "Paulo", "recorde_pessoal": 170, "posicao": 3, "data_recorde": "2021-01-01 00:00:00" }
  ]
}
```

Faltando o parâmetro (400):
```json
{ "erro": "O parâmetro \"movimento\" é obrigatório" }
```

Movimento inexistente (404):
```json
{ "erro": "Movimento não encontrado" }
```

## Estrutura

```
public/index.php          -> roteamento e entry point
src/
  Controller/             -> trata request/response
  Service/                -> regra de negócio
  Repository/             -> acesso ao banco
  Database/               -> conexão PDO (singleton)
init.sql                  -> tabelas + dados de exemplo
```

A ideia é manter cada camada com sua responsabilidade. Se precisar trocar o banco, mexe no repository e no DSN, o resto nem sabe que mudou.

## Sobre o ranking

Cada usuário pode ter vários registros num mesmo movimento. O recorde pessoal é o maior valor (MAX). Pro ranking usei `RANK()` — se dois caras levantam o mesmo peso, ficam na mesma posição e a próxima é pulada (tipo ranking olímpico). Pensei em usar `DENSE_RANK()` mas no contexto de esporte o RANK normal faz mais sentido.

## Segurança

Todas as queries usam prepared statements, nenhum input do usuário é concatenado direto no SQL. Criei índices em `movement_id` e `user_id` na tabela de records pra não ficar lento quando crescer.

## CI/CD

Este projeto utiliza GitHub Actions para automacao de CI/CD.

### Continuous Integration (CI)

O workflow de CI roda automaticamente em:
- Push para a branch `feature/ci-cd` ou `main`
- Pull Requests para `main`

O pipeline de CI inclui:
1. Validacao do composer.json
2. Instalacao das dependencias
3. Verificacao de sintaxe PHP
4. Build da imagem Docker

### Continuous Deployment (CD)

O workflow de CD roda automaticamente em:
- Push para a branch `main`

O pipeline de CD inclui:
1. Instalacao das dependencias (sem dev)
2. Build da imagem Docker de producao
3. Verificacao da imagem gerada
