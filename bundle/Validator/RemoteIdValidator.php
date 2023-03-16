<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Validator\Constraints\RemoteId;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class RemoteIdValidator extends ConstraintValidator
{
    public function __construct(private TagsService $tagsService)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof RemoteId) {
            throw new UnexpectedTypeException(
                $constraint,
                RemoteId::class,
            );
        }

        try {
            $tag = $this->tagsService->loadTagByRemoteId($value);

            if (!$constraint->payload instanceof Tag || $tag->id !== $constraint->payload->id) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%remoteId%', $value)
                    ->addViolation();
            }
        } catch (NotFoundException) {
            // Do nothing
        }
    }
}
