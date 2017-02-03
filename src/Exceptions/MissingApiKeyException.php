<?php

namespace PeterColes\XmlSoccer\Exceptions;

use Exception;

class MissingApiKeyException extends Exception
{
    public function setMessage()
    {
        return 'This request requires an API key, but none has been provided. API keys can be obtained from http://xmlsoccer.com/Register.aspx';
    }
}
