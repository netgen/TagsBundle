<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag as APITag;
use Netgen\TagsBundle\Validator\Constraints\Tag;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class TagValidator extends ConstraintValidator
{
    public function __construct(private TagsService $tagsService) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof Tag) {
            throw new UnexpectedTypeException(
                $constraint,
                Tag::class,
            );
        }

        if ($value === 0 || $value === '0') {
            if (!$constraint->allowRootTag) {
                $this->context->buildViolation($constraint->invalidMessage)
                    ->addViolation();
            }

            return;
        }

        try {
            $tag = $this->tagsService->sudo(
                static fn (TagsService $tagsService): APITag => $tagsService->loadTag((int) $value),
            );

            if ($tag->isSynonym()) {
                $this->context->buildViolation($constraint->synonymMessage)
                    ->addViolation();
            }
        } catch (NotFoundException) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%tagId%', $value)
                ->addViolation();
        }
    }
}
