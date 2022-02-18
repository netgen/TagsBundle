<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class Tag extends Constraint
{
    /**
     * @var string
     */
    public $message = 'netgen_tags.tag.no_tag';

    /**
     * @var string
     */
    public $synonymMessage = 'netgen_tags.tag.synonym';

    /**
     * @var string
     */
    public $invalidMessage = 'netgen_tags.tag.invalid_tag';

    /**
     * @var bool
     */
    public $allowRootTag = true;

    public function validatedBy(): string
    {
        return 'netgen_tags_tag';
    }
}
