<?php

namespace Netgen\TagsBundle\Validator;

use eZ\Publish\API\Repository\LanguageService;
use Netgen\TagsBundle\Validator\Constraints\Language;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

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
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof Language) {
            throw new UnexpectedTypeException($constraint, Language::class);
        }

        try {
            $this->languageService->loadLanguage($value);
        } catch (NotFoundException $e) {
            /** @var \Netgen\TagsBundle\Validator\Constraints\Language $constraint */
            $this->context->buildViolation($constraint->message)
                ->setParameter('%languageCode%', $value)
                ->addViolation();
        }
    }
}
