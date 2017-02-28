<?php

namespace PeterColes\XmlSoccer\Converters;

use stdClass;
use SimpleXMLElement;

class ObjectConverter
{
    const STRINGS = [
        'AccountInformation'
    ];

    public function handle(SimpleXMLElement $xml)
    {
        $response = new stdClass;

        foreach ($xml->children() as $child) {

            $name = $child->getName();

            if (in_array($name, self::STRINGS)) {
                $response->$name = (string) $child;
            } else {
                $response->$name[ ] = $this->processChild($name, $child);
            }
        }

        return $response;
    }

    protected function processChild($name, $child)
    {
        if ($name == 'Match') {
            return (new Objects\Match)->handle($child);
        }  

        return (new Objects\Generic)->handle($child);
    }
}
