<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class RemoteId extends Constraint
{
    /**
     * @var string
     */
    public $message = 'eztags.remote_id.remote_id_exists';

    public function validatedBy(): string
    {
        return 'eztags_remote_id';
    }
}
