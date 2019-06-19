<?php

namespace Netgen\TagsBundle\Validator\Constraints\Structs;

use Symfony\Component\Validator\Constraint;

class TagCreateStruct extends Constraint
{
    public function validatedBy(): string
    {
        return 'eztags_tag_create_struct';
    }
}
