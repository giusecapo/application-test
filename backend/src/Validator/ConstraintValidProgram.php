<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

final class ConstraintValidProgram extends Constraint
{
	public $overlappingSpeechesMessage = 'The speeches in the program must not overlap.';


	public function validatedBy()
	{
		return ConstraintValidProgramValidator::class;
	}

	public function getTargets()
	{
		return self::CLASS_CONSTRAINT;
	}
}
