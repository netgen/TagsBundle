<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class Tag extends Constraint
{
    public string $message = 'netgen_tags.tag.no_tag';

    public string  $synonymMessage = 'netgen_tags.tag.synonym';

    public string $invalidMessage = 'netgen_tags.tag.invalid_tag';

    public bool $allowRootTag = true;

    public function validatedBy(): string
    {
        return 'netgen_tags_tag';
    }
}
