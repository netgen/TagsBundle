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
    private $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    public function validate($value, Constraint $constraint): void
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
