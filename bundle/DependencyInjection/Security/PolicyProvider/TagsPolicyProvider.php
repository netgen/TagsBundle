<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\DependencyInjection\Security\PolicyProvider;

use Ibexa\Bundle\Core\DependencyInjection\Security\PolicyProvider\YamlPolicyProvider;

final class TagsPolicyProvider extends YamlPolicyProvider
{
    public function getFiles(): array
    {
        return [
            __DIR__ . '/../../../Resources/config/policies.yaml',
        ];
    }
}
