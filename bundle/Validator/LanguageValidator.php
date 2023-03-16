<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Netgen\TagsBundle\Validator\Constraints\Language;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class LanguageValidator extends ConstraintValidator
{
    private LanguageService $languageService;

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
                Language::class,
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
