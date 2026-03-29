<?php

namespace Gionin;

/**
 * Class to connection API in Gionin
 *
 * @package Gionin
 * @author Raphael Giovanini
 **/
class Api
{
    protected bool $_debug = false;

    protected string $_baseUrl = 'https://api.gionin.com';
    protected array $_queryUrl = [
        'schema' => '/v1/{user}/{app}',
        'table'  => '/v1/{user}/{app}/{table}',
    ];

    protected string $_user = '';
    protected ?string $_authUser = null;
    protected ?string $_authKey = null;
    protected string $_method = 'GET';
    protected array $_data = [];
    protected string $_url = '';
    protected array $_headers = [];

    protected string $app = '';
    protected string $table = '';

    /**
     * Set credentials
     *
     * @param string $user Master account user
     **/
    public function setUser(string $user): void
    {
        $this->_user = $user;
    }

    /**
     * Set key to credentials
     *
     * @param string $key application user password
     **/
    public function setAuthKey(string $key): void
    {
        $this->_authKey = $key;
    }

    /**
     * Set user to credentials
     *
     * @param string $user application user
     **/
    public function setAuthUser(string $user): void
    {
        $this->_authUser = $user;
    }

    /**
     * Set credentials
     *
     * @param string $user application user
     * @param string $secret application user password
     **/
    public function setCredentials(string $user, string $secret): void
    {
        $this->setAuthKey($secret);
        $this->setAuthUser($user);
    }

    /**
     * Set method to call api
     *
     * @param string $method Type of method ('GET', 'POST', 'PUT', 'DELETE')
     **/
    public function setMethod(string $method): void
    {
        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            $this->_method = $method;
        }
    }

    /**
     * Set data to sent in api
     *
     * @param array $data Data to be sent along with the request
     **/
    public function setData(array $data = []): void
    {
        $this->_data = $data;
    }

    /**
     * Set parameter app
     **/
    public function setApp(string $v): void
    {
        $this->app = $v;
    }

    /**
     * Set parameter table
     **/
    public function setTable(string $v): void
    {
        $this->table = $v;
    }

    /**
     * Set parameter url
     **/
    public function setUrl(string $type = 'table'): void
    {
        $this->_url = $this->_baseUrl . $this->_queryUrl[$type];
    }

    /**
     * Set parameters user to url in api
     **/
    protected function setUserUrl(): void
    {
        if (strlen($this->_user) < 2) {
            throw new \Exception("user not declared", 1);
        }
        $this->_url = str_replace('{user}', $this->_user, $this->_url);
    }

    /**
     * Set parameters app to url in api
     **/
    protected function setAppUrl(): void
    {
        if (strlen($this->app) < 2) {
            throw new \Exception("App not declared", 1);
        }
        $this->_url = str_replace('{app}', $this->app, $this->_url);
    }

    /**
     * Set parameters table in url to api
     **/
    protected function setTableUrl(): void
    {
        $this->setUrl('table');
        $this->setUserUrl();
        $this->setAppUrl();

        if (strlen($this->table) < 2) {
            throw new \Exception("Table not declared", 1);
        }
        $this->_url = str_replace('{table}', $this->table, $this->_url);
    }

    /**
     * Set debug mode
     **/
    public function setDebug(bool $v): void
    {
        $this->_debug = $v;
    }

    /**
     * API call method for sending requests using GET, POST, PUT or DELETE
     *
     * @return array|string|false
     **/
    public function request(): array|string|false
    {
        $url = $this->_url;

        if ($this->_method === 'GET') {
            $url .= '?' . http_build_query($this->_data);
        }

        $ch = curl_init();

        if (!empty($this->_headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
        }

        $curl_options = [
            CURLOPT_VERBOSE        => false,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $this->_authUser . ":" . $this->_authKey);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->_method);
        curl_setopt_array($ch, $curl_options);

        if ($this->_method !== 'GET') {
            $dataString = json_encode($this->_data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($dataString),
            ]);
        }

        $result = curl_exec($ch);
        $error = curl_error($ch);

        if ($this->_debug) {
            $information = curl_getinfo($ch);
            echo '<pre>';
            echo date('Y-m-d H:i:s') . "\n";
            echo 'Url: ' . $this->_url . ' - Method: ' . $this->_method . "\n";
            var_dump($this->_data, $result);
            if ($error) {
                echo $error . "\n";
            }
            echo "\n------------------\n";
            echo '</pre>';
        }

        curl_close($ch);

        if ($result) {
            return json_decode($result, true);
        }

        return $result;
    }
}
