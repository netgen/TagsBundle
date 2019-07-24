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
        parent::build($container);

        $container->addCompilerPass(new Compiler\TagViewBuilderPass());
        $container->addCompilerPass(new Compiler\DefaultStorageEnginePass());

        /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension $eZExtension */
        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addPolicyProvider(new TagsPolicyProvider());
    }
}
