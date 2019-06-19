<?php

namespace Netgen\TagsBundle\Validator\Constraints\Structs;

use Symfony\Component\Validator\Constraint;

class TagUpdateStruct extends Constraint
{
    /**
     * @var string
     */
    public $languageCode;

    public function validatedBy(): string
    {
        return 'eztags_tag_update_struct';
    }
}
