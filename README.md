# Gionin API - PHP SDK

PHP SDK para a API REST da Gionin. Fornece uma interface Model para operações CRUD com suporte a queries, paginação, ordenação, seleção de campos, retry automático, rate limiting, cache e logging.

## Requisitos

- PHP >= 8.4
- Extensões: `curl`, `json`, `mbstring`

## Instalação

```bash
composer require 1a65/gionin-api
```

## Docker

```bash
docker compose build
docker compose run --rm app composer install
docker compose run --rm test              # rodar testes
docker compose run --rm app php -a        # shell interativo
```

## Uso Rápido

```php
use Gionin\Model;

$model = new Model(
    user: 'master_user',
    appUsername: 'app_user',
    appSecret: 'app_secret',
    app: 'myapp',
    table: 'users',
);
```

### CRUD

```php
// Inserir
$response = $model->insert(['name' => 'João', 'email' => 'joao@example.com']);
echo $response->statusCode; // 201

// Buscar todos
$response = $model->findAll();
echo $response->total;       // total de registros
print_r($response->items);   // array de resultados

// Buscar com condições, campos e ordenação
$response = $model->find('all', [
    'conditions' => ['status' => 'active'],
    'fields' => ['name', 'email'],
    'order' => ['name' => 'asc'],
], page: 1, limit: 20);

// Buscar primeiro
$response = $model->findFirst(['email' => 'joao@example.com']);

// Buscar por ID
$response = $model->findById('64a1b2c3d4e5f6');

// Atualizar
$response = $model->update(['_id' => '64a1b2c3d4e5f6', 'name' => 'João Santos']);

// Deletar
$response = $model->delete(['_id' => '64a1b2c3d4e5f6']);
```

### Paginação Lazy (Iterator)

```php
// Busca páginas sob demanda, sem carregar tudo na memória
foreach ($model->findLazy(['conditions' => ['active' => true]], limit: 100) as $item) {
    echo $item['name'];
}
```

## Features Avançadas

### Retry com Backoff Exponencial

```php
use Gionin\Http\CurlClient;
use Gionin\Http\RetryClient;

$httpClient = new RetryClient(
    client: new CurlClient(),
    maxRetries: 3,
    baseDelayMs: 1000,
    multiplier: 2.0,
);

$model = new Model(
    user: 'master_user',
    appUsername: 'app_user',
    appSecret: 'app_secret',
    app: 'myapp',
    table: 'users',
    httpClient: $httpClient,
);
```

### Rate Limiting

```php
use Gionin\Http\CurlClient;
use Gionin\Http\RateLimitedClient;

$httpClient = new RateLimitedClient(
    client: new CurlClient(),
    maxTokens: 10.0,    // máximo de tokens
    refillRate: 10.0,    // tokens por segundo
);
```

### Cache (PSR-16)

```php
use Gionin\Http\CurlClient;
use Gionin\Http\CachingClient;

$httpClient = new CachingClient(
    client: new CurlClient(),
    cache: $yourPsr16Cache,   // qualquer implementação PSR-16
    defaultTtl: 300,          // 5 minutos
);
```

### Composição (Retry + Rate Limit + Cache)

```php
use Gionin\Http\{CurlClient, RetryClient, RateLimitedClient, CachingClient};

$httpClient = new RetryClient(
    client: new RateLimitedClient(
        client: new CachingClient(
            client: new CurlClient(),
            cache: $cache,
        ),
    ),
);

$model = new Model(/* ... */ httpClient: $httpClient);
```

### PSR-3 Logging

```php
use Psr\Log\LoggerInterface;

$model = new Model(/* ... */ logger: $monolog);
// Logs em nível debug para request/response
```

### PSR-18 HTTP Client

Aceita qualquer cliente PSR-18 (Guzzle, Symfony HttpClient, etc.):

```php
$model = new Model(/* ... */ httpClient: $guzzleClient);
```

## Utilitário UTF-8

```php
use Gionin\Utf8String;

Utf8String::noAccents('São Paulo');        // "Sao Paulo"
Utf8String::lowerAndNoAccents('São Paulo'); // "sao paulo"
Utf8String::isUTF8('texto');                // true
```

## Exceptions

```php
use Gionin\Exception\{
    GioninException,           // base
    ValidationException,       // campos obrigatórios faltando
    AuthenticationException,   // 401/403
    ConnectionException,       // falha de rede/cURL
};
```

## Testes & Qualidade

```bash
composer test        # PHPUnit (33 testes)
composer stan        # PHPStan (nível 5)
composer cs-check    # PHP-CS-Fixer (dry-run)
composer cs-fix      # PHP-CS-Fixer (auto-fix)
```

## Licença

MIT
