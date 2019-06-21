<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Structs;

use Netgen\TagsBundle\Validator\Constraints\Language;
use Netgen\TagsBundle\Validator\Constraints\RemoteId;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;

abstract class CreateStructValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
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
