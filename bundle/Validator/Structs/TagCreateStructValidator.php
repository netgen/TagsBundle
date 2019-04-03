<?php

namespace Netgen\TagsBundle\Validator\Structs;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Structs\TagCreateStruct as TagCreateStructConstraint;
use Netgen\TagsBundle\Validator\Constraints\Tag;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TagCreateStructValidator extends CreateStructValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     *
     * @throws \Symfony\Component\Validator\Exception\UnexpectedTypeException If the type is unexpected
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TagCreateStructConstraint) {
            throw new UnexpectedTypeException(
                $constraint,
                TagCreateStruct::class
            );
        }

        if (!$value instanceof TagCreateStruct) {
            throw new UnexpectedTypeException(
                $value,
                TagCreateStruct::class
            );
        }

        parent::validate($value, $constraint);

        /** @var \Symfony\Component\Validator\Validator\ContextualValidatorInterface $validator */
        $validator = $this->context->getValidator()->inContext($this->context);

        $validator->atPath('parentTagId')->validate(
            $value->parentTagId,
            [
                new Constraints\Type(['type' => 'numeric']),
                new Constraints\NotBlank(),
                new Tag(),
            ]
        );
    }
}
