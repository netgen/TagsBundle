<?php

declare(strict_types=1);

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\DependencyInjection\Compiler;
use Netgen\TagsBundle\DependencyInjection\Security\PolicyProvider\TagsPolicyProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NetgenTagsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new Compiler\TagViewBuilderPass());
        $container->addCompilerPass(new Compiler\DefaultStorageEnginePass());

        /** @var \Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension $ibexaCoreExtension */
        $ibexaCoreExtension = $container->getExtension('ibexa');
        $ibexaCoreExtension->addPolicyProvider(new TagsPolicyProvider());
    }
}
