<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Constraints\Structs;

use Symfony\Component\Validator\Constraint;

final class TagCreateStruct extends Constraint
{
    public function validatedBy(): string
    {
        return 'netgen_tags_tag_create_struct';
    }
}
