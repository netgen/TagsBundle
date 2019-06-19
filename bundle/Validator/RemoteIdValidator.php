<?php

namespace Netgen\TagsBundle\Validator;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Validator\Constraints\RemoteId;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RemoteIdValidator extends ConstraintValidator
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof RemoteId) {
            throw new UnexpectedTypeException(
                $constraint,
                RemoteId::class
            );
        }

        try {
            $tag = $this->tagsService->loadTagByRemoteId($value);

            if (!$constraint->payload instanceof Tag || $tag->id !== $constraint->payload->id) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%remoteId%', $value)
                    ->addViolation();
            }
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }
}
