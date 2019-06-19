<?php

namespace Netgen\TagsBundle\Validator\Structs;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Netgen\TagsBundle\Validator\Constraints\Language;
use Netgen\TagsBundle\Validator\Constraints\RemoteId;
use Netgen\TagsBundle\Validator\Constraints\Structs\TagUpdateStruct as TagUpdateStructConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TagUpdateStructValidator extends ConstraintValidator
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof TagUpdateStructConstraint) {
            throw new UnexpectedTypeException(
                $constraint,
                TagUpdateStruct::class
            );
        }

        if (!$value instanceof TagUpdateStruct) {
            throw new UnexpectedTypeException(
                $value,
                TagUpdateStruct::class
            );
        }

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
            $value->getKeyword($constraint->languageCode),
            [
                new Constraints\Type(['type' => 'string']),
                new Constraints\NotBlank(),
            ]
        );

        $validator->atPath('remoteId')->validate(
            $value->remoteId,
            [
                new Constraints\Type(['type' => 'string']),
                new Constraints\NotBlank(),
                new RemoteId(
                    [
                        'payload' => $constraint->payload,
                    ]
                ),
            ]
        );

        if ($value->mainLanguageCode !== null) {
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
}
