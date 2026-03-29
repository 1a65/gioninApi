<?php

namespace Gionin;

use Gionin\Exception\AuthenticationException;
use Gionin\Exception\ValidationException;
use Gionin\Http\CurlClient;
use Gionin\Response\ApiResponse;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Uri;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class to connection API in Gionin
 *
 * @package Gionin
 * @author Raphael Giovanini
 **/
class Api
{
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

    protected ClientInterface $httpClient;
    protected LoggerInterface $logger;

    public function __construct(?ClientInterface $httpClient = null, ?LoggerInterface $logger = null)
    {
        $this->httpClient = $httpClient ?? new CurlClient();
        $this->logger = $logger ?? new NullLogger();
    }

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

    public function setHttpClient(ClientInterface $client): void
    {
        $this->httpClient = $client;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Set parameters user to url in api
     **/
    protected function setUserUrl(): void
    {
        if (strlen($this->_user) < 2) {
            throw new ValidationException("user not declared");
        }
        $this->_url = str_replace('{user}', $this->_user, $this->_url);
    }

    /**
     * Set parameters app to url in api
     **/
    protected function setAppUrl(): void
    {
        if (strlen($this->app) < 2) {
            throw new ValidationException("App not declared");
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
            throw new ValidationException("Table not declared");
        }
        $this->_url = str_replace('{table}', $this->table, $this->_url);
    }

    /**
     * API call method for sending requests using GET, POST, PUT or DELETE
     **/
    public function request(): ApiResponse
    {
        $url = $this->_url;

        if ($this->_method === 'GET') {
            $url .= '?' . http_build_query($this->_data);
        }

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->_authUser . ':' . $this->_authKey),
        ];

        $body = '';
        if ($this->_method !== 'GET') {
            $body = json_encode($this->_data);
            $headers['Content-Type'] = 'application/json';
            $headers['Content-Length'] = (string) strlen($body);
        }

        foreach ($this->_headers as $header) {
            if (str_contains($header, ':')) {
                [$name, $value] = explode(':', $header, 2);
                $headers[trim($name)] = trim($value);
            }
        }

        $this->logger->debug('Gionin API request', [
            'method' => $this->_method,
            'url'    => $url,
            'data'   => $this->_data,
        ]);

        $request = new Request($this->_method, new Uri($url), $headers, $body);
        $response = $this->httpClient->sendRequest($request);

        $statusCode = $response->getStatusCode();
        $rawBody = (string) $response->getBody();
        $data = json_decode($rawBody, true);

        $this->logger->debug('Gionin API response', [
            'statusCode' => $statusCode,
            'body'       => $rawBody,
        ]);

        if ($statusCode === 401 || $statusCode === 403) {
            throw new AuthenticationException("Authentication failed (HTTP {$statusCode})");
        }

        return new ApiResponse($statusCode, $data, $rawBody);
    }
}
