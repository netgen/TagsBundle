<?php

namespace Netgen\TagsBundle;

use Netgen\TagsBundle\DependencyInjection\Compiler\TagViewBuilderPass;
use Netgen\TagsBundle\DependencyInjection\Security\PolicyProvider\TagsPolicyProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenTagsBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TagViewBuilderPass());

        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addPolicyProvider(new TagsPolicyProvider());
    }
}
