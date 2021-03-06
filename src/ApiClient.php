<?php

namespace PeterColes\XmlSoccer;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use PeterColes\XmlSoccer\Converters\ObjectConverter;
use PeterColes\XmlSoccer\Exceptions\ApiKeyNotAcceptedException;
use PeterColes\XmlSoccer\Exceptions\ApiThrottlingException;
use PeterColes\XmlSoccer\Exceptions\InvalidXmlException;
use PeterColes\XmlSoccer\Exceptions\MissingApiKeyException;
use PeterColes\XmlSoccer\Exceptions\RequestFailedException;

class ApiClient
{
    /**
     * Default API endpoint.
     */
    protected $apiEndpoint;

    /**
     * Subscriber's API key.
     */
    protected $apiKey;

    /**
     * Guzzle client instance.
     */
    protected $guzzleClient;

    /**
     * The raw XML response returned from the last XMLSoccer request.
     */
    protected $response;

    /**
     * Optional (recommended) setting of an API key when a new instance is instantiated.
     * Setting the API endpoint, default or demo according to the (optional) parameter.
     * Advanced users may wish to a Guzzle client with their own configuration settings
     * but this will rarely be needed.
     *
     * @param string | null $apiKey
     * @param boolean       $demo
     * @param GuzzleClient  $guzzleClient
     */
    public function __construct($apiKey = null, $demo = false, $guzzleClient = null)
    {
        $this->setApiKey($apiKey);
        $this->setApiEndpoint($demo);
        $this->initGuzzleClient($guzzleClient);
    }

    /**
     * Override default API endpoint.
     *
     * @param $apiEndpoint
     */
    public function setApiEndpoint($demo = false)
    {
        $this->apiEndpoint = 'http://www.xmlsoccer.com/FootballData'.($demo ? 'Demo' : '').'.asmx';
    }

    /**
     * Set or override the API key.
     *
     * @param $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Accept the requested method and its parameters, make the request to XML Soccer and validate the response.
     *
     * @param $name
     * @param $params
     * @return SimpleXMLElement
     */
    public function __call($method, $params)
    {
        $this->response = $this->request($this->buildUri($method), $this->prepareParams($method, $params));

        if (false !== strpos($this->response, 'Api-key not accepted')) {
            throw new ApiKeyNotAcceptedException;
        }

        if (false !== strpos($this->response, 'To avoid misuse of the service')) {
            throw new ApiThrottlingException;
        }

        return $this;
    }

    public function get()
    {
        return $this->response;
    }

    public function xml()
    {
        try {
            $xmlObject = simplexml_load_string($this->response);
        } catch (Exception $e) {
            throw new InvalidXmlException;
        }

        return $xmlObject;
    }

    public function object()
    {
        return (new ObjectConverter)->handle($this->xml());
    }

    public function json()
    {
        return json_encode($this->object());
    } 

    /**
     * Build the base URI for the API from its endpoint and the resource being requested.
     *
     * @param $method
     * @return string
     */
    protected function buildUri($method)
    {
        return $this->apiEndpoint.'/'.ucfirst($method);
    }

    /**
     * Almost all API calls require an API Key so we add it to the parameters.
     *
     * @param $params
     * @return string
     */
    protected function prepareParams($method, $params)
    {
        if ('ImAlive' == ucfirst($method)) {
            return null;
        }

        if (!$this->apiKey) {
            throw new MissingApiKeyException;
        }

        return array_merge([ 'apiKey' => $this->apiKey ], $params[ 0 ] ?? [ ]);
    }

    /**
     * Initialise or inject an instance of the Guzzle client.
     *
     * @return GuzzleClient
     */
    protected function initGuzzleClient($guzzleClient)
    {
        $this->guzzleClient = $guzzleClient ?? new GuzzleClient;
    }

    /**
     * Make the request to the XML Soccer service and validate response.
     *
     * @param  string        $uri
     * @param  array | null  $params
     * @throws RequestFailedException
     * @return SimpleXMLElement
     */
    protected function request($uri, $params)
    {
        try {
            $response = $this->guzzleClient->get($uri, [ 'query' => $params ]);
        } catch (Exception $e) {
            throw new RequestFailedException;
        }

        return $response->getBody();
    }
}
