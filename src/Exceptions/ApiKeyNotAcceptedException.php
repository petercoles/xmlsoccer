<?php

namespace PeterColes\XmlSoccer\Exceptions;

use Exception;

class ApiKeyNotAcceptedException extends Exception
{
    public function setMessage()
    {
        return 'The XML Soccer service was not able to verify your API key.';
    }
}
