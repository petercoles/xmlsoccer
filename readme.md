# XML Soccer API Client

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/9cb65160-ed5f-4aad-a449-f1369365fe35/mini.png)](https://insight.sensiolabs.com/projects/9cb65160-ed5f-4aad-a449-f1369365fe35)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/petercoles/xml-soccer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/petercoles/xml-soccer/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/petercoles/xml-soccer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/petercoles/xml-soccer/build-status/master)
[![License](http://img.shields.io/:license-mit-blue.svg)](http://doge.mit-license.org)

The purpose of this package is to provide easy access to the XML Soccer API for retrieving historic and live data about football (soccer) matches.

## Requirements

PHP 7.0+, Composer, SimpleXML

## Installation

The package is designed to be integrated into projects using Composer as their dependancy manager. To add to your project, navigate the root of the project and execute ```composer require petercoles/xml-soccer```.

## Usage

To make an API request, you will need an API key. XML Soccer generously provides free demo API keys to allow you to test out the service. You can [get one here](http://xmlsoccer.com/Demo.aspx).

Once you have an API key, instantiate an instance of the Api client and pass the key in as the first parameter. If your API key is for the demo system, set the second paramter to true. If you have full access, the 2nd parameter should be omitted. For example:

```
use PeterColes\XmlSoccer\ApiClient;

class MyRequest()
{
    ...

    public function getLiveData()
    {
        $client = new ApiClient('YOUR_API_KEY_HERE', true));

        $xml = $client->getLiveScore();

        // process $xml as a SimpleXMLElement Object
    }

    ...
}
```

A full list of the methods available can be found in the [XML Soccer service description](http://www.xmlsoccer.com/FootballData.asmx). More [general documentation for the service](https://xmlsoccer.zendesk.com/hc/en-us) is also available.

Many methods require parameters. These are passed as an associative array. For example:

```
$client->getNextMatchOddsByLeague([ 'league' => 3 ]);
$client->GetFixturesByLeagueAndSeason([ 'league' => 3, 'seasonDateString' => '0506' ]);
```

Responses are received as [SimpleXMLElement](http://php.net/manual/en/book.simplexml.php) Objects. These can be iterated through as shown in the following crude example:

```
try {
    $xml = $this->client->getLiveScore();
} catch(\Exception $e) {
    exit('XML Soccer Exception: '.$e->getMessage());
}

foreach ($xml->Match as $match) {
    $homeScore = empty($match->HomeGoalDetails) ? 0 : count(explode(';', $match->HomeGoalDetails)) - 1;
    $awayScore = empty($match->AwayGoalDetails) ? 0 : count(explode(';', $match->AwayGoalDetails)) - 1;
    echo "$match->HomeTeam v $match->AwayTeam : $homeScore-$awayScore";
}
```

## The Test Suite

Two sets of tests are provided, unit tests, which simply check that the client operates as it should, and integration tests, which connect (mostly) to the demo XML Soccer service and confirm that requests are transmitted and responses are as expected.

Before running either set of tests, copy the ```phpunit.xml.dist``` to ```phpunit.xml```. If you want to run the integration tests you will also need to edit this file to insert your XML Soccer API Key towards the end, where indicated. This new file will be excluded from any git commits that you make so your API Key will remain secret even if you make public contributions to this package.

To run the unit tests alone, simply execute ```vendor/bin/phpunit tests/UnitTest```. Similarly, to run the integration tests only, execute ```vendor/bin/phpunit tests/IntegrationTest```, or to run both, just ```vendor/bin/phpunit```.

if you only have a demo service key, then one of the integration tests may fail as it can only be run against the live service. If this happens, add the ```--exclude-group live``` flag to suppress that <whispering>unimportant</whispering> test.

Both live and demo APIs are throttled to avoid excessive load on the service's servers. Most calls to the Demo API require a 5 second gap between requests (for the live system the gaps are larger and variable). The integration test suite enforces this delay before running each affected test.
