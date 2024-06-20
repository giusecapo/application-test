<?php

declare(strict_types=1);

namespace App\Validator;

use App\Document\Event;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ConstraintValidProgramValidator extends ConstraintValidator
{
	public function __construct()
	{
	}

	public function validate($value, Constraint $constraint)
	{
		if (!$constraint instanceof ConstraintValidProgram) {
			throw new UnexpectedTypeException($constraint, ConstraintValidProgram::class);
		}

		if (!$value instanceof Event) {
			throw new UnexpectedValueException($value, Event::class);
		}

		// TODO: Ensure no overlapping speech times.
		// At any given moment during the event, only one speech should be occurring.
		// If overlaps are detected, add the following violation to the context 
		// to display an appropriate error message to the API consumer.
		$this->context
			->buildViolation($constraint->overlappingSpeechesMessage)
			->addViolation();
	}
}
