<?php

namespace PeterColes\XmlSoccer\Exceptions;

use Exception;

class InvalidXmlException extends Exception
{
    public function setMessage()
    {
        return 'The structure of the XML received in response to the API request was not valid.';
    }
}
