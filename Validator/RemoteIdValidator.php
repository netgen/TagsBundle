<?php

namespace Netgen\TagsBundle\Validator;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Validator\Constraints\RemoteId;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

class RemoteIdValidator extends ConstraintValidator
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param \Symfony\Component\Validator\Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof RemoteId) {
            throw new UnexpectedTypeException($constraint, RemoteId::class);
        }

        try {
            $tag = $this->tagsService->loadTagByRemoteId($value);

            if (!$constraint->payload instanceof Tag || $tag->id !== $constraint->payload->id) {
                /** @var \Netgen\TagsBundle\Validator\Constraints\RemoteId $constraint */
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%remoteId%', $value)
                    ->addViolation();
            }
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }
}
