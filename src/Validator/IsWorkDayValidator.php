<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsWorkDayValidator extends ConstraintValidator
{

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof isWorkDay) {
            throw new UnexpectedTypeException($constraint, isWorkday::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!($value instanceof \DateTimeImmutable)) {
            // throw this exception if your validator cannot handle the passed type so that it can be marked as invalid
            throw new UnexpectedValueException($value, 'DateType');
        }

        $day = $value->format('w');

        // if day is sunday or saturday
        if (in_array($day, ["0", "6"])) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
