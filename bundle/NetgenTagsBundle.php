<?php

declare(strict_types=1);

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\DependencyInjection\Compiler\TagViewBuilderPass;
use Netgen\TagsBundle\DependencyInjection\Security\PolicyProvider\TagsPolicyProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenTagsBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new TagViewBuilderPass());

        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addPolicyProvider(new TagsPolicyProvider());
    }
}
