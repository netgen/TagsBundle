<?php

namespace Netgen\TagsBundle\Validator\Constraints\Structs;

use Symfony\Component\Validator\Constraint;

class TagCreateStruct extends Constraint
{
    /**
     * Returns the name of the class that validates this constraint.
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'eztags_tag_create_struct';
    }
}
