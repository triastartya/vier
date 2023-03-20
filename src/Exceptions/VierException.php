<?php

namespace Viershaka\Vier\Exceptions;

use Exception;

/**
 * Exception root class
 */
class VierException extends Exception 
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