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
            ->arrayNode('tag_view')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('cache')
                        ->info('Whether to use tag view page cache or not (Last-Modified based)')
                        ->defaultTrue()
                    ->end()
                    ->booleanNode('ttl_cache')
                        ->info('Whether to use TTL cache for tag view page (i.e. Max-Age response header)')
                        ->defaultTrue()
                    ->end()
                    ->integerNode('default_ttl')
                        ->info('Default TTL cache value for tag view page')
                        ->min(0)
                        ->defaultValue(60)
                    ->end()
                    ->scalarNode('template')
                        ->info('Default template used to generate tag view page')
                        ->cannotBeEmpty()
                        ->defaultValue('NetgenTagsBundle:tag:view.html.twig')
                    ->end()
                    ->scalarNode('pagelayout')
                        ->info('Default pagelayout used in tag view page')
                        ->cannotBeEmpty()
                        ->defaultValue('eZDemoBundle::pagelayout.html.twig')
                    ->end()
                    ->scalarNode('path_prefix')
                        ->info('Default path prefix to use when generating tag view links')
                        ->cannotBeEmpty()
                        ->defaultValue('/tags/view')
                    ->end()
                    ->arrayNode('related_content_list')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('limit')
                                ->info('Number of related content displayed per page in the tag view')
                                ->min(0)
                                ->defaultValue(10)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
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
            ->end()
            ->arrayNode('routing')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('enable_tag_router')
                        ->info('Enables or disables tag router. It can be disabled for example in legacy admin siteacccess.')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('admin')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('pagelayout')
                        ->info('Default pagelayout template for admin interface')
                        ->cannotBeEmpty()
                        ->defaultValue('NetgenTagsBundle:admin:pagelayout.html.twig')
                    ->end()
                    ->integerNode('children_limit')
                        ->info('Limit to tag children list in admin interface')
                        ->min(0)
                        ->defaultValue(25)
                    ->end()
                    ->integerNode('related_content_limit')
                        ->info('Limit to tag related content list in admin interface')
                        ->min(0)
                        ->defaultValue(25)
                    ->end()
                    ->arrayNode('field')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('autocomplete_limit')
                                ->info('Limit to autocomplete list in field edit interface')
                                ->min(0)
                                ->defaultValue(24)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
