<?php

namespace Netgen\TagsBundle;

use eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyBundleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Netgen\TagsBundle\DependencyInjection\Security\PolicyProvider\TagsPolicyProvider;

class NetgenTagsBundle extends Bundle implements LegacyBundleInterface
{
    /**
     * Returns a list of legacy extension names.
     *
     * @return array List of legacy extension names to inject to ActiveExtensions
     */
    public function getLegacyExtensionsNames()
    {
        return array('eztags');
    }

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

        $eZExtension = $container->getExtension('ezpublish');
        if (method_exists($eZExtension, 'addPolicyProvider')) {
            $eZExtension->addPolicyProvider(new TagsPolicyProvider());
        }
    }
}
