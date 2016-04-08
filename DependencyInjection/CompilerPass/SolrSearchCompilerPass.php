<?php

namespace Netgen\TagsBundle\DependencyInjection\CompilerPass;

use Netgen\TagsBundle\DependencyInjection\NetgenTagsExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SolrSearchCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!NetgenTagsExtension::isSolrPlatformSearchActivated($container)) {
            return;
        }

        $classParameter = 'ezpublish.fieldType.indexable.eztags.class';
        if ($container->getParameter($classParameter) != 'eZ\Publish\Core\FieldType\Unindexed') {
            return;
        }

        $container->setParameter($classParameter, 'Netgen\TagsBundle\Core\FieldType\Tags\SearchField');
    }
}
