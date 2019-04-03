<?php

namespace Netgen\TagsBundle\Validator\Structs;

use Netgen\TagsBundle\Validator\Constraints\Language;
use Netgen\TagsBundle\Validator\Constraints\RemoteId;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;

abstract class CreateStructValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        /** @var \Symfony\Component\Validator\Validator\ContextualValidatorInterface $validator */
        $validator = $this->context->getValidator()->inContext($this->context);

        $validator->atPath('alwaysAvailable')->validate(
            $value->alwaysAvailable,
            [
                new Constraints\Type(['type' => 'bool']),
                new Constraints\NotNull(),
            ]
        );

        $validator->atPath('keyword')->validate(
            $value->getKeyword(),
            [
                new Constraints\Type(['type' => 'string']),
                new Constraints\NotBlank(),
            ]
        );

        $validator->atPath('remoteId')->validate(
            $value->remoteId,
            [
                new Constraints\Type(['type' => 'string']),
                new RemoteId(),
            ]
        );

        $validator->atPath('mainLanguageCode')->validate(
            $value->mainLanguageCode,
            [
                new Constraints\Type(['type' => 'string']),
                new Constraints\NotBlank(),
                new Language(),
            ]
        );
    }
}
