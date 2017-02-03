<?php

namespace PeterColes\XmlSoccer\Exceptions;

use Exception;

class ApiThrottlingException extends Exception
{
    public function setMessage()
    {
        return "Your API call has been throttled to avoid overloading XML Soccer's servers. Details of the request rate limits applicable to this service can be found at https://xmlsoccer.zendesk.com/hc/en-us/articles/202852961-Time-interval-limits";
    }
}
