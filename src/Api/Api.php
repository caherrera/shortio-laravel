<?php


namespace Shortio\Laravel\Api;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Shortio\Laravel\ConnectorInterface;


/**
 * Class Api
 *
 * @package Shortio\Laravel\Api
 */
abstract class Api
{
    /**
     * @var Response
     */
    protected $responses = [];
    /**
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    private $id;
    /**
     * @var array
     */
    private $config;
    /**
     * @var string
     */
    private $baseUrl;
    private $connector;
    /**
     * @var Arrayable
     */
    private $requestHeaders;
    private $requests = [];

    public function __construct(ConnectorInterface $connector, $id = null)
    {
        $this->setConnector($connector)
             ->setConfig($connector->config())
             ->setBaseUrl($this->prepareBaseUrl())
             ->setHeaders($this->prepareHeaders());
    }

    protected function setHeaders(Arrayable $headers = null)
    {
        $this->requestHeaders = $headers;

        return $this;
    }

    /**
     * @return string
     */
    public function prepareBaseUrl($url = null)
    {
        $host     = $this->getHost();
        $protocol = $this->getProtocol();
        $path     = $this->getPath();
        $basePath = collect(["$protocol:/", $host, $path]);
        if ($url) {
            $basePath->merge((array)($url));
        }

        return $basePath->join('/');
    }

    public function getHost()
    {
        return $this->config['api'];
    }

    public function getProtocol()
    {
        return $this->config['secure'] ? 'https' : 'http';
    }

    public function getPath()
    {
        return $this->path ?? Str::slug(Str::plural($this->className()));
    }

    public function className()
    {
        return Str::singular(class_basename($this));
    }

    public function prepareHeaders()
    {
        return collect($this->requestHeaders)->merge($this->getConnector()->getHeaders());
    }

    /**
     * @return ConnectorInterface
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * @param  mixed  $connector
     *
     * @return Api
     */
    public function setConnector(ConnectorInterface $connector)
    {
        $this->connector = $connector;


        return $this;
    }

    public function client()
    {
        return $this->getConnector();
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param  mixed  $config
     *
     * @return Api
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function get($id)
    {
        return $this->processRequest('get', $id);
    }

    /**
     * @param $method
     * @param  string  $url
     * @param  array  $query
     *
     * @return array|mixed
     * @throws \Exception
     */
    private function processRequest($method, $url = '', $query = [])
    {
        $response          = $this->__callRequest($method, $url, $query);
        $this->responses[] = $response;

        if ($response->successful()) {
            return $response->json();
        } elseif ($response->serverError()) {
            throw new \Exception('Server Error');
        } else {
            throw new \Exception($response->body());
        }
    }

    /**
     * @param $method
     * @param  string  $url
     * @param  array  $query
     *
     * @return \Illuminate\Http\Client\Response
     */
    private function __callRequest($method, $url = '', $query = [])
    {
        $response = $this->prepareRequest()->asJson()->$method($url, $query);

        return $response;
    }

    /**
     * @return \Illuminate\Http\Client\PendingRequest
     */
    private function prepareRequest()
    {
        $request = Http::baseUrl($this->getBaseUrl())
                       ->withHeaders($this->getHeaders());

        return $this->requests[] = $request;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;

        return $this;
    }

    protected function getHeaders()
    {
        return $this->getConnector()->getHeaders();
    }

    public function all()
    {
        return $this->processRequest('get');
    }

    public function save(array $data = [])
    {
        return $this->isNew() ? $this->create($data) : $this->update($this->getId(), $data);
    }

    public function isNew()
    {
        return $this->id === null;
    }

    public function create(array $data = [])
    {
        return $this->post('', $data);
    }

    public function post($url, array $data = [])
    {
        return $this->processRequest('post', $url, $data);
    }

    public function update($id, array $data = [])
    {
        return $this->post($this->prepareUpdateUrl($id), $data);
    }

    /**
     * @return array|int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function prepareGenericUrl($id)
    {
        return $id;
    }

    public function __call($name, $arguments)
    {
        switch (true) {
            case preg_match("/prepare(.*)Url/", $name, $match):
                return call_user_func_array([$this, 'prepareGenericUrl'], $arguments);
        }
    }

    public function delete($id)
    {
        return $this->processRequest('delete', $id, []);
    }

}
