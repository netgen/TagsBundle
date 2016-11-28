<?php

namespace Netgen\TagsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;

class Configuration extends SiteAccessConfiguration
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('netgen_tags');

        $this->generateScopeBaseNode($rootNode)
            ->arrayNode('tag_view_match')
                ->info('Template selection settings when displaying a tag')
                ->useAttributeAsKey('key')
                ->normalizeKeys(false)
                ->prototype('array')
                    ->useAttributeAsKey('key')
                    ->normalizeKeys(false)
                    ->info("View selection rule sets, grouped by view type. Key is the view type (e.g. 'full', 'line', ...)")
                    ->prototype('array')
                        ->children()
                            ->scalarNode('template')->info('Your template path, as MyBundle:subdir:my_template.html.twig')->end()
                            ->scalarNode('controller')
                                ->info(
<<<'EOT'
Use custom controller instead of the default one to display a tag matching your rules.
You can use the controller reference notation supported by Symfony.
EOT
                                )
                                ->example('MyBundle:MyControllerClass:view')
                            ->end()
                            ->arrayNode('match')
                                ->info('Condition matchers configuration')
                                ->isRequired()
                                ->useAttributeAsKey('key')
                                ->prototype('variable')->end()
                            ->end()
                            ->arrayNode('params')
                                ->info(
<<<'EOT'
Arbitrary params that will be passed in the TagView object, manageable by tag view provider.
Those params will NOT be passed to the resulting view template by default.
EOT
                                )
                                ->example(
                                    array(
                                        'foo' => '%some.parameter.reference%',
                                        'osTypes' => array('osx', 'linux', 'windows'),
                                    )
                                )
                                ->useAttributeAsKey('key')
                                ->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('edit_views')
                ->info('List of available edit views in field edit interface')
                ->useAttributeAsKey('key')
                ->normalizeKeys(false)
                ->prototype('array')
                    ->children()
                        ->scalarNode('identifier')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('name')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
