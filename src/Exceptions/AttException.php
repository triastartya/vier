<?php

namespace Att\Workit\Exceptions;

use Exception;

/**
 * Exception root class
 */
class AttException extends Exception 
{
    public function __construct($message = 'Error', $code = 400) {
        parent::__construct($message, $code);
    }

    public function render()
    {
        return responisme()->withMessage($this->getMessage())
            ->withHttpCode($this->getCode())
            ->build(false);
    }
}