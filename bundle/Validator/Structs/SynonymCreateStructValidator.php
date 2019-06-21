<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Structs;

use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Structs\SynonymCreateStruct as SynonymCreateStructConstraint;
use Netgen\TagsBundle\Validator\Constraints\Tag;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SynonymCreateStructValidator extends CreateStructValidator
{
    public function validate($value, Constraint $constraint): void
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

        $validator = $this->context->getValidator()->inContext($this->context);

        $validator->atPath('mainTagId')->validate(
            $value->mainTagId,
            [
                new Constraints\Type(['type' => 'int']),
                new Constraints\NotBlank(),
                new Tag(['allowRootTag' => false]),
            ]
        );
    }
}
