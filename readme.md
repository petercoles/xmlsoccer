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

## JSON Conversion

The XML Soccer service returns responses in XML. No problem in the PHP world, we have SimpleXML to hekp us with that. And if that works for you, feel free to use this package in it's default mode.

But if you're like me and prefer (need) to work with a more friendly format (take a bow JSON), then from version 1.1 we've got your back. Just prefix your requests with the json() method and your responses will be magically converted into JSON. For example:
```
$xml = $client->getLiveScore();
$json = $client->json()->getLiveScore();

```

Most response objects are simple lists of attributes. So the getAllLeagues will return an object containing a League attribute, which in turn will contain an array of objects each describing one league:
```
{ League: [
        {
            Id: "1",
            Name: "English Premier League",
            Country: "England",
            ...
            IsCup: "false"

        },
        ...
        {
            Id: "57",
            Name: "Ligaat AL",
            Country: "Isreal",
            ...
            IsCup: "false"
        }
    ]
}

```

But match data, e.g. retrieved from Live Score or Historic Fixtures, have more complex structures with information about goals, cards, substititions and players all collapsed into strings that have to be unpacked if you're to make sense of them.

This package takes care of this too so when converted to JSON ...

The XML goal string
```
<HomeGoalDetails>50': Riccardo Orsolini;38': Andrea Favilli;4':Own A N Other;</HomeGoalDetails>
```
would be returned as:
```
...
    HomeGoalDetails: [
        {
            Minute: 50,
            Player: 'Riccardo Orsolini',
            Own: false
        },
        {
            Minute: 38,
            Player: 'Andrea Favilli',
            Own: false
        },
        {
            Minute: 4,
            Player: 'A N Other',
            Own: true
        }
    ],
...
```
and similarly for cards, substitutions and player lists.

Note that for match data only, the data is cast to an appropriate type, i.e. integers for numeric data and boolean where appropriate, rather than everything defaulting to strings. 

## The Test Suite

Three sets of tests are provided, general unit tests, which simply check that the client operates as it should, integration tests, which connect (mostly) to the demo XML Soccer service and confirm that requests are transmitted and responses are as expected, and a special suite focussed on JSON conversion.

Before running either set of tests, copy the ```phpunit.xml.dist``` to ```phpunit.xml```. If you want to run the integration tests you will also need to edit this file to insert your XML Soccer API Key towards the end, where indicated. This new file will be excluded from any git commits that you make so your API Key will remain secret even if you make public contributions to this package.

To run the unit tests alone, simply execute ```vendor/bin/phpunit tests/UnitTest```. Similarly, to run the integration tests only, execute ```vendor/bin/phpunit tests/IntegrationTest```, or to run both, just ```vendor/bin/phpunit```.

if you only have a demo service key, then one of the integration tests may fail as it can only be run against the live service. If this happens, add the ```--exclude-group live``` flag to suppress that <whispering>unimportant</whispering> test.

Both live and demo APIs are throttled to avoid excessive load on the service's servers. Most calls to the Demo API require a 5 second gap between requests (for the live system the gaps are larger and variable). The integration test suite enforces this delay before running each affected test.
