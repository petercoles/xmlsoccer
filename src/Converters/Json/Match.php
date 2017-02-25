<?php

namespace PeterColes\XmlSoccer\Converters\Json;

use SimpleXMLElement;

class Match
{
    const PLAYERS = [
        'HomeLineupGoalkeeper',
        'HomeLineupDefense',
        'HomeLineupMidfield',
        'HomeLineupForward',
        'HomeLineupSubstitutes',
        'AwayLineupGoalkeeper',
        'AwayLineupDefense',
        'AwayLineupMidfield',
        'AwayLineupForward',
        'AwayLineupSubstitutes'
    ];

    const GOAL_DETAILS = [
        'HomeGoalDetails',
        'AwayGoalDetails'
    ];

    const CARDS = [
        'HomeTeamYellowCardDetails',
        'HomeTeamRedCardDetails',
        'AwayTeamYellowCardDetails',
        'AwayTeamRedCardDetails'
    ];

    const SUBSTITUTIONS = [
        'HomeSubDetails',
        'AwaySubDetails'
    ];

    const NUMERIC = [
        'Id',
        'FixtureMatch_Id',
        'Round',
        'Spectators',
        'HomeTeam_Id',
        'HomeGoals',
        'HalfTimeHomeGoals',
        'HomeShots',
        'HomeShotsOnTarget',
        'HomeFouls',
        'HomeYellowCards',
        'HomeRedCards',
        'AwayTeam_Id',
        'AwayGoals',
        'HalfTimeAwayGoals',
        'AwayShots',
        'AwayShotsOnTarget',
        'AwayFouls',
        'AwayYellowCards',
        'AwayRedCards'
    ];

    public function handle(SimpleXMLElement $match)
    {
        $object = [ ];

        foreach ($match as $child) {
            $name = $child->getName();
            $object[ $name ] = $this->processAtrribute($name, $child);
        }

        return $object;
    }

    protected function processAtrribute($name, $data)
    {
        if (in_array($name, self::PLAYERS)) {
            return $this->players($data);
        }

        if (in_array($name, self::GOAL_DETAILS)) {
            return array_map([ $this, 'goals' ], $this->toArray($data));
        }

        if (in_array($name, self::CARDS)) {
            return array_map([ $this, 'cards' ], $this->toArray($data));
        }

        if (in_array($name, self::SUBSTITUTIONS)) {
            return array_map([ $this, 'substitutions' ], $this->toArray($data));        }

        if (in_array($name, self::NUMERIC)) {
            return (int) $data;
        }

        if ($name == 'HasBeenRescheduled') {
            return (boolean) $data;
        }

        return (string) $data;
    }

    protected function players($data)
    {
        return array_map('ltrim', $this->toArray($data));
    }

    protected function goals($goal)
    {
        list($minute, $player) = explode("':", $goal);
        if (substr($player, 0, 3) == 'Own') {
            return [
                'Minute' => (int) $minute,
                'Player' => ltrim(str_replace('Own', '', $player)),
                'Own' => true
            ];
        } else {
            return [
                'Minute' => (int) $minute,
                'Player' => ltrim($player),
                'Own' => false
            ];
        }
    }

    protected function cards($card)
    {
        list($minute, $player) = explode("': ", $card);
        return [ 'Minute' => (int) $minute, 'Player' => $player ];
    }

    protected function substitutions($substitution)
    {
        list($minute, $player) = explode("': ", $substitution);
        if (strpos($player, 'in ') === 0) {
            return [ 'Minute' => (int) $minute, 'Type' => 'In', 'Player' => substr($player, 3) ];
        } elseif (strpos($player, 'out ') === 0) {
            return [ 'Minute' => (int) $minute, 'Type' => 'Out', 'Player' => substr($player, 4) ];
        }
    }

    protected function toArray($data)
    {
        $data = $this->cleanse($data);

        return empty($data) ? [ ] : explode(';', rtrim($data,";"));
    }

    /**
     * Very occasionally the data has spurious hard spaces that need to be removed
     */
    protected function cleanse($data)
    {
        return str_replace('&nbsp;', '', $data);
    }
}
