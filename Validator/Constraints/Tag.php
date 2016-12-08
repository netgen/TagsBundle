<?php

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
    public $invalidMessage = 'eztags.tag.invalid';

    /**
     * @var bool
     */
    public $allowRootTag = true;

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
