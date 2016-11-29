<?php

namespace Netgen\TagsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Tag extends Constraint
{
    /**
     * @var string
     */
    public $message = 'eztags.validator.tag';

    /**
     * Returns the name of the class that validates this constraint.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'eztags_tag';
    }
}
