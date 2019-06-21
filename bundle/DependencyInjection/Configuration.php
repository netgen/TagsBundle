<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\Configuration as SiteAccessConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class Configuration extends SiteAccessConfiguration
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netgen_tags');

        /** @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

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
                        ->defaultValue('@NetgenTags/tag/view.html.twig')
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
                            ->booleanNode('return_content_info')
                                ->info('Setting to control if ContentInfo objects will be returned instead of Content')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('tag_view_match')
                ->info('Template selection settings when displaying a tag')
                ->useAttributeAsKey('key')
                ->normalizeKeys(false)
                ->arrayPrototype()
                    ->useAttributeAsKey('key')
                    ->normalizeKeys(false)
                    ->info("View selection rule sets, grouped by view type. Key is the view type (e.g. 'full', 'line', ...)")
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('template')->info('Your template path, as @My/subdir/my_template.html.twig')->end()
                            ->scalarNode('controller')
                                ->info('Use custom controller instead of the default one to display a tag matching your rules. You can use the controller reference notation supported by Symfony.')
                                ->example('MyBundle:MyControllerClass:view')
                            ->end()
                            ->arrayNode('match')
                                ->info('Condition matchers configuration')
                                ->isRequired()
                                ->useAttributeAsKey('key')
                                ->variablePrototype()->end()
                            ->end()
                            ->arrayNode('params')
                                ->info('Arbitrary params that will be passed in the TagView object, manageable by tag view provider. Those params will NOT be passed to the resulting view template by default.')
                                ->example(
                                    [
                                        'foo' => '%some.parameter.reference%',
                                        'osTypes' => ['osx', 'linux', 'windows'],
                                    ]
                                )
                                ->useAttributeAsKey('key')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('edit_views')
                ->info('List of available edit views in field edit interface')
                ->useAttributeAsKey('key')
                ->normalizeKeys(false)
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('identifier')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('name')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('template')
                            ->defaultValue(null)
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('admin')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('pagelayout')
                        ->info('Default pagelayout template for admin interface')
                        ->cannotBeEmpty()
                        ->defaultValue('@NetgenTags/admin/pagelayout.html.twig')
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
                ->end()
            ->end()
            ->arrayNode('field')
                ->addDefaultsIfNotSet()
                ->children()
                    ->integerNode('autocomplete_limit')
                        ->info('Limit to autocomplete list in field edit interface')
                        ->min(0)
                        ->defaultValue(25)
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
