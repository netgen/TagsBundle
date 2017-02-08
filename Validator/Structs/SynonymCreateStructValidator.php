<?php

namespace Netgen\TagsBundle\Validator\Structs;

use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Structs\SynonymCreateStruct as SynonymCreateStructConstraint;
use Netgen\TagsBundle\Validator\Constraints\Tag;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SynonymCreateStructValidator extends CreateStructValidator
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
        if (!$constraint instanceof SynonymCreateStructConstraint) {
            throw new UnexpectedTypeException(
                $constraint,
                SynonymCreateStruct::class
            );
        }

        if (!$value instanceof SynonymCreateStruct) {
            throw new UnexpectedTypeException(
                $value,
                SynonymCreateStruct::class
            );
        }

        parent::validate($value, $constraint);

        /** @var \Symfony\Component\Validator\Validator\ContextualValidatorInterface $validator */
        $validator = $this->context->getValidator()->inContext($this->context);

        $validator->atPath('mainTagId')->validate(
            $value->mainTagId,
            array(
                new Constraints\Type(array('type' => 'numeric')),
                new Constraints\NotBlank(),
                new Tag(array('allowRootTag' => false)),
            )
        );
    }
}
