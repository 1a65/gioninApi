<?php

namespace Gionin;

/**
 * Class for using API with a model
 *
 * @package Gionin
 * @author  Raphael Giovanini
 **/
class Model extends Api
{
    protected array $_findTypes = [
        'all',
        'first',
    ];
    protected array $_conditions = [];
    protected array $_fields = [];
    protected array $_order = [
        'default' => 'asc',
    ];

    /**
     * Total from last query
     **/
    public int $total = 0;

    public function __construct(
        string $user = '',
        string $appUsername = '',
        string $appSecret = '',
        string $app = '',
        string $table = '',
        bool $debug = false
    ) {
        $this->setUser($user);
        $this->setCredentials($appUsername, $appSecret);

        if ($app !== '') {
            $this->setApp($app);
        }
        if ($table !== '') {
            $this->setTable($table);
        }
        if ($debug) {
            $this->setDebug($debug);
        }
    }

    protected function setOperation(string $method, array $data): array|string|false
    {
        $this->setTableUrl();
        $this->setMethod($method);
        $this->setData($data);
        return $this->request();
    }

    public function reset(): void
    {
        $this->_fields = [];
        $this->_order = ['default' => 'asc'];
    }

    protected function setOrder(array $order = []): void
    {
        if ($order !== []) {
            $this->_order = $order;
        }
    }

    protected function setFields(array $fields = []): void
    {
        if ($fields !== []) {
            $this->_fields = $fields;
        }
    }

    public function insert(array $data): array|string|false
    {
        return $this->setOperation('POST', $data);
    }

    public function update(array $data): array|string|false
    {
        return $this->setOperation('PUT', $data);
    }

    public function delete(array $data): array|string|false
    {
        return $this->setOperation('DELETE', $data);
    }

    private function treatData(array $data): void
    {
        if (isset($data['order'])) {
            unset($data['order']);
        }
        if (isset($data['fields'])) {
            $this->treatFields($data);
            unset($data['fields']);
        }
        if (isset($data['conditions'])) {
            $this->_conditions = $data['conditions'];
            $this->treatOrder($data);
            $this->treatFields($data);
            return;
        }
        $this->_conditions = $data;
    }

    private function treatOrder(array $data): void
    {
        if (isset($data['order']) && is_array($data['order'])) {
            $this->setOrder($data['order']);
        }
    }

    private function treatFields(array $data): void
    {
        if (isset($data['fields']) && is_array($data['fields'])) {
            $this->setFields($data['fields']);
        }
    }

    public function find(string $type = 'all', array $data = [], int $page = 1, int $limit = 20): array|string|false
    {
        if (!in_array($type, $this->_findTypes)) {
            throw new \Exception("Error type for find", 1);
        }

        $this->reset();
        $this->treatOrder($data);
        $this->treatData($data);

        $data['json'] = json_encode([
            'q'      => $this->_conditions,
            'page'   => $page,
            'limit'  => $limit,
            'fields' => $this->_fields,
            'order'  => $this->_order,
        ]);

        $return = $this->setOperation('GET', $data);

        if ($return && is_array($return)) {
            $this->total = $return['_total'] ?? 0;
            unset($return['_total']);

            if ($type === 'first' && isset($return[0])) {
                return $return[0];
            }
        }

        return $return;
    }

    public function findAll(array $data = [], int $page = 1, int $limit = 1000000): array|string|false
    {
        return $this->find('all', $data, $page, $limit);
    }

    public function findFirst(array $data = []): array|string|false
    {
        return $this->find('first', $data);
    }

    public function findById(string $id): array|string|false
    {
        return $this->find('first', ['_id' => $id], 1, 1);
    }
}
