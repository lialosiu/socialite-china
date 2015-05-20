<?php namespace Lialosiu\SocialiteChina\Exception;

use Exception;

class WeiboOAuthException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        $message = '#' . $code . ': ' . $message;
        parent::__construct($message, $code, $previous);
    }

}