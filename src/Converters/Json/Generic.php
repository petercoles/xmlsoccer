<?php

namespace PeterColes\XmlSoccer\Converters\Json;

class Generic
{
    public function handle($item)
    {
        foreach ($item as $child) {
            $object[ $child->getName() ] = (string) $child;
        }

        return $object;
    }
}
