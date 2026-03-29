# Gionin API - PHP Wrapper

PHP wrapper para a API REST da Gionin. Fornece uma interface Model para operações CRUD com suporte a queries, paginação, ordenação e seleção de campos.

## Requisitos

- PHP >= 8.1
- Extensões: `curl`, `json`, `mbstring`

## Instalação

```bash
composer require 1a65/gionin-api
```

## Uso Rápido

```php
use Gionin\Model;

$model = new Model(
    user: 'master_user',
    appUsername: 'app_user',
    appSecret: 'app_secret',
    app: 'myapp',
    table: 'users'
);
```

### Inserir

```php
$result = $model->insert([
    'name' => 'João Silva',
    'email' => 'joao@example.com',
]);
```

### Buscar todos

```php
$results = $model->findAll();
```

### Buscar com condições

```php
$results = $model->find('all', [
    'conditions' => ['status' => 'active'],
    'fields' => ['name', 'email'],
    'order' => ['name' => 'asc'],
], page: 1, limit: 20);

echo $model->total; // total de registros encontrados
```

### Buscar primeiro

```php
$user = $model->findFirst(['email' => 'joao@example.com']);
```

### Buscar por ID

```php
$user = $model->findById('64a1b2c3d4e5f6');
```

### Atualizar

```php
$result = $model->update([
    '_id' => '64a1b2c3d4e5f6',
    'name' => 'João Santos',
]);
```

### Deletar

```php
$result = $model->delete(['_id' => '64a1b2c3d4e5f6']);
```

## Uso direto da API

```php
use Gionin\Api;

$api = new Api();
$api->setUser('master_user');
$api->setCredentials('app_user', 'app_secret');
$api->setApp('myapp');
$api->setTable('users');
$api->setTableUrl();
$api->setMethod('GET');
$api->setData(['json' => '{"q":{},"page":1,"limit":10}']);

$response = $api->request();
```

## Utilitário UTF-8

```php
use Gionin\Utf8String;

$clean = Utf8String::noAccents('São Paulo');        // "Sao Paulo"
$lower = Utf8String::lowerAndNoAccents('São Paulo'); // "sao paulo"
$isUtf = Utf8String::isUTF8('texto');                // true
```

## Debug

```php
$model = new Model(
    user: 'master_user',
    appUsername: 'app_user',
    appSecret: 'app_secret',
    app: 'myapp',
    table: 'users',
    debug: true
);
```

## Licença

MIT
