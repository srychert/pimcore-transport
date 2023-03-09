<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

class IsWorkDay extends Constraint
{
    public string $message = 'The date cannot be set to a holiday: it can be Monday-Friday';
    public string $mode;
}
