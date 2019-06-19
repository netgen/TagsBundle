<?php

namespace Netgen\TagsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Language extends Constraint
{
    /**
     * @var string
     */
    public $message = 'eztags.language.no_language';

    public function validatedBy(): string
    {
        return 'eztags_language';
    }
}
