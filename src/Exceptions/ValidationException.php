<?php

namespace Viershaka\Vier\Exceptions;

use Exception;
use Illuminate\Validation\Validator;

class ValidationException extends VierException 
{
    protected $validator;

    public function __construct($msg, Validator $validator = null) {
        $message = $msg;
        if (isset($validator)) {
            $message = $this->formatMessage($msg, $validator);
            $this->validator = $validator;
        }
        parent::__construct($message, 200);
    }

    public function formatMessage($msg, $validator) {
        if (isset($msg)) {
            $msg = $msg . ": ";
        } else {
            $msg = '';
        }
        return $msg . implode(' | ', $validator->messages()->all());
    }

    protected function getValidatorErrors() : array
    {
        return $this->validator
            ? $this->validator->errors()->getMessages()
            : [];
    }

    public function render()
    {
        return responisme()->withMessage($this->getMessage())
            ->withData([
                'errors' => $this->getValidatorErrors()
            ])
            ->withHttpCode($this->getCode())
            ->build(false);
    }
}