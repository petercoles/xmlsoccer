<?php

namespace Tests\XmlSoccer;

use stdClass;
use PeterColes\XmlSoccer\ApiClient;

final class IntegrationTest extends TestCase
{
    const DEMO_API_TIME_LIMIT = 5;

    /**
     * Integration testing will make calls to the XML Soccer demo service.
     * This requires an API Key to be initialised in the phpunit.xml config file.
     * see the readme file for more details.
     */
    public function setUp()
    {
        $this->client = new ApiClient(getenv('XMLSOCCER_API_KEY'), true);
    }

    /**
     * The ImAlive() API request doesn't appear to be available for the demo service
     * so, exceptionally, we override the endpoint and test against the full service.
     * This test can be excluded by excluding the "live" group when you run the test,
     * e.g. phpunit tests/IntegrationTest --exclude-group live
     *
     * @group live
     */
    public function testCanVerifyServiceIsAlive()
    {
        $this->client->setApiEndpoint();
        $this->client->setApiKey('anything');

        $response = $this->client->ImAlive();

        $this->assertStringContains('I am alive!', (string) $response);
    }

    public function testCanMakeApiConnection()
    {
        $response = $this->client->checkApiKey();

        $this->assertStringContains('you have access', (string) $response);
    }

    /**
     * Test is skipped if demo system API throttling would be triggered
     */
    public function testCanMakeRequestWithOutParameters()
    {
        if ($this->throttled('testCanMakeRequestWithOutParameters')) {
            $this->markTestSkipped('Skipped to avoid throttling errors');
        }

        $response = $this->client->getAllLeagues();

        $this->assertObjectHasAttribute('League', $response);
    }

    /**
     * Test is skipped if demo system API throttling would be triggered
     */
    public function testCanMakeRequestWithParameters()
    {
        if ($this->throttled('testCanMakeRequestWithParameters')) {
            $this->markTestSkipped('Skipped to avoid throttling errors');
        }

        $response = $this->client->GetAllTeamsByLeagueAndSeason([ 'league' => 3, 'seasonDateString' => '1516' ]);

        $this->assertObjectHasAttribute('Team', $response);
    }

    /**
     * Utility function to help avoid triggering API throttling when testing
     */
    protected function throttled($test)
    {
        $log = __DIR__.'/../throttle.log';     

        $timestamps = file_exists($log) ? json_decode(file_get_contents($log)) : new stdClass;

        $previous = $timestamps->$test ?? 0;

        $timestamps->$test = time();

        file_put_contents($log, json_encode($timestamps));

        return ($timestamps->$test - $previous) <= self::DEMO_API_TIME_LIMIT;
    }
}
