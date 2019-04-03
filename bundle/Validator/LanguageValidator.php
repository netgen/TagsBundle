<?php

namespace Netgen\TagsBundle\Validator;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LanguageService;
use Netgen\TagsBundle\Validator\Constraints\Language;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LanguageValidator extends ConstraintValidator
{
    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $languageService;

    /**
     * LanguageValidator constructor.
     *
     * @param \eZ\Publish\API\Repository\LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

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
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof Language) {
            throw new UnexpectedTypeException(
                $constraint,
                Language::class
            );
        }

        try {
            $this->languageService->loadLanguage($value);
        } catch (NotFoundException $e) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%languageCode%', $value)
                ->addViolation();
        }
    }
}
