<?php

namespace App\Exception;

use Throwable;

class BaseException extends \Exception
{
    private array $errors = [];

    public function __construct($message = null, $code = 0)
    {
        parent::__construct(is_array($message) ? '' : $message, $code);

        if(is_array($message)) {
            $this->errors = $message ?? [];
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}
