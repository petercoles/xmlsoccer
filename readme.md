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

## Basic Usage

To make an API request, you will need an API key. XML Soccer generously provides free demo API keys to allow you to test out the service. You can [get one here](http://xmlsoccer.com/Demo.aspx).

Once you have an API key, instantiate an instance of the Api client and pass the key in as the first parameter. For requests to the demo system (e.g. if you don't yet have a paid-for subscription and are using a free API key or are testing and want more generous API throttling limits), set the second paramter to true. To access the full service, just omit the 2nd parameter. For example:

```
use PeterColes\XmlSoccer\ApiClient;

class MyRequest()
{
    ...

    public function getLiveData()
    {
        $client = new ApiClient('YOUR_API_KEY_HERE', true));  // for the demo service

        $response = $client->getLiveScore()->get();
    }

    ...
}
```
This will return the raw data exactly as it's received from the XMLSoccer service, in pure XML.

A full list of the methods available can be found in the [XML Soccer service description](http://www.xmlsoccer.com/FootballData.asmx). More [general documentation for the service](https://xmlsoccer.zendesk.com/hc/en-us) is also available.

Many methods require parameters. These are passed as an associative array. For example:

```
$client->getNextMatchOddsByLeague([ 'league' => 3 ]);
$client->GetFixturesByLeagueAndSeason([ 'league' => 3, 'seasonDateString' => '0506' ]);
```

## Transforming the Response

If you like parsing XML data then to ->get() method may be all you need, feel free to skip this section.

Alternatively, this package makes available three more friendly ways to receiev the response.

### SimpleXML Object

Using the xml() method in place of get() will cause the response to be converted into a [SimpleXMLElement](http://php.net/manual/en/book.simplexml.php) object. These can be iterated through as shown in the following crude example:

```
try {
    $xml = $client->getLiveScore()->xml();
} catch(\Exception $e) {
    exit('XML Soccer Exception: '.$e->getMessage());
}

foreach ($xml->Match as $match) {
    $homeScore = empty($match->HomeGoalDetails) ? 0 : count(explode(';', $match->HomeGoalDetails)) - 1;
    $awayScore = empty($match->AwayGoalDetails) ? 0 : count(explode(';', $match->AwayGoalDetails)) - 1;
    echo "$match->HomeTeam v $match->AwayTeam : $homeScore-$awayScore";
}
```

### PHP Object

If you're not a big fan of XML, no problem. Using the object() method will transform the response into a PHP object. For example,```$object = $client->getLiveScore()->object();```.

Most response objects are simple lists of attributes. So the getAllLeagues will return an object containing a League attribute, which in turn will contain an array of objects each describing one league (i.e. competition as it includes cups and hybrids such as The EUFA Champions League):
```
object(stdClass) {
    ["League"]=>
    array(n) {
        [0]=>
        object(stdClass) {
            ["Id"] => "1",
            ["Name"] => "English Premier League",
            {"Country"] => "England",
            ...
            ["IsCup"] => "false"
        },
        ...
        [n]=>
        object {
            ["Id"] => "57",
            ["Name"] => "Ligaat AL",
            {"Country"] => "Isreal",
            ...
            ["IsCup"] => "false"
        }
    }
    ["AccountInformation"]=> string "Blah."
}

```

But match data, e.g. retrieved from Live Score or Historic Fixtures, have more complex structures with information about goals, cards, substititions and players, all collapsed into strings in the default response, that have to be unpacked if you're to make sense of them.

This package takes care of this too so when converted to an object this XML goal string
```
<HomeGoalDetails>50': Riccardo Orsolini;38': Andrea Favilli;4':Own A N Other;</HomeGoalDetails>
```
would be returned as:
```
...
    ["HomeGoalDetails"]=>
    array(3) {
        [0]=>
        object(stdClass) {
            ["Minute"] => 50,
            ["Player"] => "Riccardo Orsolini",
            ["Own"] => false
        },
        [1]=>
        object(stdClass) {
            ["Minute"] => 38,
            ["Player"] => "Andrea Favilli",
            ["Own"] => false
        },
        [2]=>
        object(stdClass) {
            ["Minute"] => 4,
            ["Player"] => "A N Othe",
            ["Own"] => true
        }
...
```
and the data within it accessed such as this
```
foreach ($object->Match[23]->HomeGoalDetails as $goal) {
    echo $goal->Minute;   
    echo $goal->Player;   
    echo $goal->Own;   
}
```

... and similarly for cards, substitutions and player lists.

Note that for match data only, the data is cast to an appropriate type, i.e. integers for numeric data and boolean where appropriate, rather than everything defaulting to strings.

### JSON

If you'd prefer to receive the object in JSON notation (i.e. as a JSON-encoded string), use the json() method instead, e.g. ```$json = $client->getLiveScore()->json();```.

## Upgrading

In version 1, data was returned by the call to an XMLSoccer-like method such as getLiveScore(). In version 2 however, it's returned by get(), xml(), object() or json() (see above for descriptions) according to your taste.

If you were using object() or json() with version 1 simply to move this call to the end of the request, e.g.```$json = $client->json()->getLiveScore();``` becomes ```$json = $client->getLiveScore()->json();```.

If you weren't using any conversions, then just append xml() to return a simpleXML object, as was the default in version 1, e.g. ```$xml = $client->getLiveScore();``` becomes ```$xml = $client->getLiveScore()->xml();```.

## The Test Suite

Four sets of tests are provided, general unit tests, which simply check that the client operates as it should, integration tests, which connect (mostly) to the demo XML Soccer service and confirm that requests are transmitted and responses are as expected, and special suites for PHP object and JSON conversion.

Before running either set of tests, copy the ```phpunit.xml.dist``` to ```phpunit.xml```. If you want to run the integration tests you will also need to edit this file to insert your XML Soccer API Key towards the end, where indicated. This new file will be excluded from any git commits that you make so your API Key will remain secret even if you make public contributions to this package.

To run the unit tests alone, simply execute ```vendor/bin/phpunit tests/UnitTest```. Similarly, to run the integration tests only, execute ```vendor/bin/phpunit tests/IntegrationTest```, or to run everything, just ```vendor/bin/phpunit```.

if you only have a demo service key, then one of the integration tests may fail as it can only be run against the live service. If this happens, add the ```--exclude-group live``` flag to suppress that <whispering>unimportant</whispering> test.

Both live and demo APIs are throttled to avoid excessive load on the service's servers. Most calls to the Demo API require a 5 second gap between requests (for the live system the gaps are larger and depend on the specific request being sent). The integration test suite enforces this delay before running each affected test so if you run it, expect some 5 second pauses in execution.
