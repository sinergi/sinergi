<?php

namespace Sinergi\Users\User\Exception;

use Sinergi\Users\User\UserException;

class InvalidUserException extends UserException
{
    private $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Invalid user', 1200);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
