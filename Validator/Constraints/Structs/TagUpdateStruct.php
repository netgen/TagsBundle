<?php

namespace Netgen\TagsBundle\Validator\Constraints\Structs;

use Symfony\Component\Validator\Constraint;

class TagUpdateStruct extends Constraint
{
    /**
     * @var string
     */
    public $languageCode;

    /**
     * Returns the name of the class that validates this constraint.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'eztags_tag_update_struct';
    }
}
