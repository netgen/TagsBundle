<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Tag extends Constraint
{
    /**
     * @var string
     */
    public $message = 'eztags.tag.no_tag';

    /**
     * @var string
     */
    public $synonymMessage = 'eztags.tag.synonym';

    /**
     * @var string
     */
    public $invalidMessage = 'eztags.tag.invalid_tag';

    /**
     * @var bool
     */
    public $allowRootTag = true;

    public function validatedBy(): string
    {
        return 'eztags_tag';
    }
}
