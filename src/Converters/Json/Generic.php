<?php

namespace PeterColes\XmlSoccer\Converters\Json;

use SimpleXMLElement;

class Generic
{
    public function handle(SimpleXMLElement $item)
    {
        $object = [ ];

        foreach ($item as $child) {
            $object[ $child->getName() ] = (string) $child;
        }

        return $object;
    }
}
