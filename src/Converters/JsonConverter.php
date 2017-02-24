<?php

namespace PeterColes\XmlSoccer\Converters;

use stdClass;

class JsonConverter
{
    const STRINGS = [
        'AccountInformation'
    ];

    public function handle($xml)
    {
        $response = new stdClass;

        foreach ($xml->children() as $child) {

            $name = $child->getName();

            if (in_array($name, self::STRINGS)) {
                $response->$name = (string) $child;
            } else {
                $response->$name[] = $this->processChild($name, $child);
            }
        }

        return json_encode($response);
    }

    protected function processChild($name, $child)
    {
        if ($name == 'Match') {
            return (new Json\Match)->handle($child);
        }  

        return (new Json\Generic)->handle($child);
    }
}
