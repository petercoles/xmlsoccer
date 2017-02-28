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

final class JsonConversionTest extends TestCase
{
    public function testCanConvertLeagues()
    {
        $mock = new MockHandler([ new Response(200, [], $this->getData('all-leagues')) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);

        $response = $client->GetAllLeagues()->json();
        $this->assertIsJson($response);

        $response = json_decode($response);
        $this->assertObjectHasAttribute('League', $response);
        $this->assertEquals(3, count($response->League));
        $this->assertEquals('English Premier League', $response->League[0]->Name);
        $this->assertEquals('Scotland', $response->League[2]->Country);
        $this->assertEquals('Blah.', $response->AccountInformation);
    }

    public function testCanConvertMatches()
    {
        $mock = new MockHandler([ new Response(200, [], $this->getData('live-scores')) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->GetLiveScore()->json();
        $this->assertIsJson($response);

        $homeGoals = [
            (object) [ 'Minute' => 50, 'Player' => 'Riccardo Orsolini', 'Own' => false ],
            (object) [ 'Minute' => 38, 'Player' => 'Andrea Favilli', 'Own' => false ],
            (object) [ 'Minute' => 4, 'Player' => 'A N Other', 'Own' => true ],
        ];

        $homeYellowCards = [
            (object) [ 'Minute' => 55, 'Player' => 'Andrea Mengoni' ],
            (object) [ 'Minute' => 36, 'Player' => 'Blazey Augustyn' ],
            (object) [ 'Minute' => 34, 'Player' => 'Gian Filippo Felicioli' ],
        ];

        $awaySubstitutions = [
            (object) [ 'Minute' => 46, 'Type' => 'Out', 'Player' => 'Simone Emmanuello' ],
            (object) [ 'Minute' => 46, 'Type' => 'In', 'Player' => 'Armando Vajushi' ],
        ];

        $response = json_decode($response);
        $this->assertObjectHasAttribute('Match', $response);
        $this->assertEquals(2, count($response->Match));
        $this->assertEquals('Serie B', $response->Match[0]->League);
        $this->assertEquals([ 'Bright Addae', 'Francesco Cassata', 'Luigi Giorgi' ], $response->Match[0]->HomeLineupMidfield);
        $this->assertEquals("57'", $response->Match[0]->Time);
        $this->assertEquals('Not started', $response->Match[1]->Time);
        $this->assertEquals(3, $response->Match[0]->HomeGoals);
        $this->assertEquals($homeGoals, $response->Match[0]->HomeGoalDetails);
        $this->assertEquals($homeYellowCards, $response->Match[0]->HomeTeamYellowCardDetails);
        $this->assertEquals([], $response->Match[0]->AwayTeamYellowCardDetails);
        $this->assertEquals([], $response->Match[0]->HomeSubDetails);
        $this->assertEquals($awaySubstitutions, $response->Match[0]->AwaySubDetails);
    }

    public function testCanConvertOdds()
    {
        $mock = new MockHandler([ new Response(200, [], $this->getData('odds')) ]);
        $handler = HandlerStack::create($mock);

        $guzzleClient = new Client([ 'handler' => $handler ]);

        $client = new ApiClient('MADE_UP_API_KEY', true, $guzzleClient);
        $response = $client->GetOddsByFixture()->json();
        $this->assertIsJson($response);

        $response = json_decode($response);
        $this->assertObjectHasAttribute('Odds', $response);
        $this->assertEquals(4, count($response->Odds));
        $this->assertEquals(12.75, $response->Odds[1]->_10Bet_Home_Away);
        $this->assertObjectHasAttribute('WilliamHill_Away', $response->Odds[1]);
        $this->assertObjectNotHasAttribute('WilliamHill_Away', $response->Odds[2]);
    }
}
