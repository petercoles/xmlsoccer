<?php

namespace Tests\XmlSoccer;

use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PeterColes\XmlSoccer\ApiClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\RequestException;

final class UnitTest extends TestCase
{
    public function testCannotMakerequestWithoutApiKey()
    {
        $this->expectException(\PeterColes\XmlSoccer\Exceptions\MissingApiKeyException::class);

        $client = new ApiClient();
        $client->GetAllLeagues();
    }

    public function testCanCreateRequestForDemoService()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([ new Response(200, [], $this->truncatedAllLeaguesResponse()) ]);
        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->GetAllLeagues();

        $this->assertEquals('GET', $container[0]['request']->getMethod());
        $this->assertEquals('http', $container[0]['request']->getUri()->getScheme());
        $this->assertEquals('www.xmlsoccer.com', $container[0]['request']->getUri()->getHost());
        $this->assertEquals('/FootballDataDemo.asmx/GetAllLeagues', $container[0]['request']->getUri()->getPath());
        $this->assertEquals('apiKey=MADE_UP_API_KEY', $container[0]['request']->getUri()->getQuery());
    }

    public function testCanCreateRequestForLiveService()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([ new Response(200, [], $this->truncatedAllLeaguesResponse()) ]);
        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', null, $guzzleClient);
        $response = $client->GetAllLeagues();

        $this->assertEquals('GET', $container[0]['request']->getMethod());
        $this->assertEquals('http', $container[0]['request']->getUri()->getScheme());
        $this->assertEquals('www.xmlsoccer.com', $container[0]['request']->getUri()->getHost());
        $this->assertEquals('/FootballData.asmx/GetAllLeagues', $container[0]['request']->getUri()->getPath());
        $this->assertEquals('apiKey=MADE_UP_API_KEY', $container[0]['request']->getUri()->getQuery());
    }

    public function testSuccessfulRequestReturnsSimpleXmlElement()
    {
        $mock = new MockHandler([ new Response(200, [], $this->truncatedAllLeaguesResponse()) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->GetAllLeagues();

        $this->assertInstanceOf('SimpleXMLElement', $response);
    }

    public function testCanAcceptCamelCaseMethods()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([ new Response(200, [], $this->truncatedAllLeaguesResponse()) ]);
        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', null, $guzzleClient);
        $response = $client->getAllLeagues();

        $this->assertEquals('/FootballData.asmx/GetAllLeagues', $container[0]['request']->getUri()->getPath());
    }

    public function testApiKeyNotAcceptedThrowsException()
    {
        $this->expectException(\PeterColes\XmlSoccer\Exceptions\ApiKeyNotAcceptedException::class);

        $mock = new MockHandler([ new Response(200, [], $this->apiExceptionResponse('Api-key not accepted')) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->GetAllLeagues();
    }

    public function testApiThrottlingThrowsException()
    {
        $this->expectException(\PeterColes\XmlSoccer\Exceptions\ApiThrottlingException::class);

        $mock = new MockHandler([ new Response(200, [], $this->apiExceptionResponse('To avoid misuse of the service')) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->GetAllLeagues();
    }

    public function testInvalidXMLThrowsException()
    {
        $this->expectException(\PeterColes\XmlSoccer\Exceptions\InvalidXmlException::class);

        $mock = new MockHandler([ new Response(200, [], substr($this->truncatedAllLeaguesResponse(), 0, 1000)) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->getAllLeagues();
    }

    public function testRequestFailedThrowsException()
    {
        $this->expectException(\PeterColes\XmlSoccer\Exceptions\RequestFailedException::class);

        $mock = new MockHandler([ new Response(500) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->getAllLeagues();
    }

    protected function truncatedAllLeaguesResponse()
    {
        return <<<'EOT'
<?xml version="1.0" encoding="utf-8"?>
<XMLSOCCER.COM>
    <League>
        <Id>1</Id>
        <Name>English Premier League</Name>
        <Country>England</Country>
        <Historical_Data>Yes</Historical_Data>
        <Fixtures>Yes</Fixtures>
        <Livescore>Yes</Livescore>
        <NumberOfMatches>6310</NumberOfMatches>
        <LatestMatch>2017-02-01T21:00:00+00:00</LatestMatch>
        <IsCup>false</IsCup>
    </League>
    <League>
        <Id>2</Id>
        <Name>English League Championship</Name>
        <Country>England</Country>
        <Historical_Data>Yes</Historical_Data>
        <Fixtures>Yes</Fixtures>
        <Livescore>Yes</Livescore>
        <NumberOfMatches>9266</NumberOfMatches>
        <LatestMatch>2017-02-02T20:45:00+00:00</LatestMatch>
        <IsCup>false</IsCup>
    </League>
    <League>
        <Id>3</Id>
        <Name>Scottish Premier League</Name>
        <Country>Scotland</Country>
        <Historical_Data>Yes</Historical_Data>
        <Fixtures>Yes</Fixtures>
        <Livescore>Yes</Livescore>
        <NumberOfMatches>3784</NumberOfMatches>
        <LatestMatch>2017-02-01T20:45:00+00:00</LatestMatch>
        <IsCup>false</IsCup>
    </League>
    <AccountInformation>Blah.</AccountInformation>
</XMLSOCCER.COM>
EOT;
    }

    protected function apiExceptionResponse($message)
    {
        return <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<XMLSOCCER.COM>Blah, blah, "$message", blah, blah, blah</XMLSOCCER.COM>
EOT;
    }
}
