<?php

namespace Netgen\TagsBundle;

use eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyBundleInterface;
use Netgen\TagsBundle\DependencyInjection\SolrSearchCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenTagsBundle extends Bundle implements LegacyBundleInterface
{
    public function getLegacyExtensionsNames()
    {
        return array('eztags');
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SolrSearchCompilerPass());
    }
}
