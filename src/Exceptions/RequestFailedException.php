<?php

namespace PeterColes\XmlSoccer\Exceptions;

use Exception;

class RequestFailedException extends Exception
{
    public function setMessage()
    {
        return 'Your request to the XML Soccer service failed';
    }
}
