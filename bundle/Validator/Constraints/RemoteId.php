<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class RemoteId extends Constraint
{
    public string $message = 'netgen_tags.remote_id.remote_id_exists';

    public function validatedBy(): string
    {
        return 'netgen_tags_remote_id';
    }
}
