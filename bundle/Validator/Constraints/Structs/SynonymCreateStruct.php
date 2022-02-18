<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Constraints\Structs;

use Symfony\Component\Validator\Constraint;

final class SynonymCreateStruct extends Constraint
{
    public function validatedBy(): string
    {
        return 'netgen_tags_synonym_create_struct';
    }
}
