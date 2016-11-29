<?php

namespace Netgen\TagsBundle\Validator\Structs;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Language;
use Netgen\TagsBundle\Validator\Constraints\RemoteId;
use Netgen\TagsBundle\Validator\Constraints\Structs\TagCreateStruct as TagCreateStructConstraint;
use Netgen\TagsBundle\Validator\Constraints\Tag;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TagCreateStructValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TagCreateStructConstraint) {
            throw new UnexpectedTypeException($constraint, TagCreateStructConstraint::class);
        }

        if (!$value instanceof TagCreateStruct) {
            throw new UnexpectedTypeException($value, TagCreateStruct::class);
        }

        /** @var \Symfony\Component\Validator\Validator\ContextualValidatorInterface $validator */
        $validator = $this->context->getValidator()->inContext($this->context);

        $validator->atPath('parentTagId')->validate(
            $value->parentTagId,
            array(
                new Constraints\Type(array('type' => 'scalar')),
                new Constraints\NotBlank(),
                new Tag(),
            )
        );

        $validator->atPath('alwaysAvailable')->validate(
            $value->alwaysAvailable,
            array(
                new Constraints\Type(array('type' => 'boolean')),
                new Constraints\NotNull(),
            )
        );

        $validator->atPath('mainLanguageCode')->validate(
            $value->mainLanguageCode,
            array(
                new Constraints\Type(array('type' => 'string')),
                new Constraints\NotBlank(),
                new Language(),
            )
        );

        $validator->atPath('getKeyword')->validate(
            $value->getKeyword(),
            array(
                new Constraints\Type(array('type' => 'string')),
                new Constraints\NotBlank(),
            )
        );

        $validator->atPath('remoteId')->validate(
            $value->remoteId,
            array(
                new Constraints\Type(array('type' => 'string')),
                new RemoteId(),
            )
        );
    }
}
