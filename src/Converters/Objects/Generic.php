<?php

namespace PeterColes\XmlSoccer\Converters\Objects;

use stdClass;
use SimpleXMLElement;

class Generic
{
    public function handle(SimpleXMLElement $item)
    {
        $object = new stdClass;

        foreach ($item as $child) {
            $object->{$child->getName()} = (string) $child;
        }

        return $object;
    }
}
