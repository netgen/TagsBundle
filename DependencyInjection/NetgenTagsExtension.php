<?php

namespace Netgen\TagsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;

/**
 * This is the class that loads and manages the bundle configuration.
 */
class NetgenTagsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');
        $loader->load('rest/services.yml');
        $loader->load('fieldtypes.yml');
        $loader->load('persistence.yml');
        $loader->load('papi.yml');
        $loader->load('default_settings.yml');
        $loader->load('pagerfanta.yml');
        $loader->load('templating.yml');
        $loader->load('view.yml');
        $loader->load('limitations.yml');
        $loader->load('storage_engines/legacy.yml');
        $loader->load('admin/controllers.yml');
        $loader->load('admin/templating.yml');
        $loader->load('forms.yml');
        $loader->load('validators.yml');
        $loader->load('param_converters.yml');

        $loader->load('platformui/default_settings.yml');
        $loader->load('platformui/services.yml');

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));

        if (in_array('EzSystemsEzPlatformSolrSearchEngineBundle', $activatedBundles)) {
            $loader->load('storage_engines/solr/criterion_visitors.yml');
        }

        if (in_array('EzPublishLegacySearchEngineBundle', $activatedBundles)) {
            $loader->load('storage_engines/legacy/search_query_handlers.yml');
        }

        $this->processSemanticConfig($container, $config);
    }

    /**
     * Processes semantic config and translates it to container parameters.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     */
    protected function processSemanticConfig(ContainerBuilder $container, $config)
    {
        $processor = new ConfigurationProcessor($container, 'eztags');
        $processor->mapConfig(
            $config,
            function ($config, $scope, ContextualizerInterface $c) {
                $c->setContextualParameter('tag_view.cache', $scope, $config['tag_view']['cache']);
                $c->setContextualParameter('tag_view.ttl_cache', $scope, $config['tag_view']['ttl_cache']);
                $c->setContextualParameter('tag_view.default_ttl', $scope, $config['tag_view']['default_ttl']);
                $c->setContextualParameter('tag_view.template', $scope, $config['tag_view']['template']);
                $c->setContextualParameter('tag_view.pagelayout', $scope, $config['tag_view']['pagelayout']);
                $c->setContextualParameter('tag_view.path_prefix', $scope, $config['tag_view']['path_prefix']);
                $c->setContextualParameter('tag_view.related_content_list.limit', $scope, $config['tag_view']['related_content_list']['limit']);

                $c->setContextualParameter('routing.enable_tag_router', $scope, $config['routing']['enable_tag_router']);

                $c->setContextualParameter('admin.pagelayout', $scope, $config['admin']['pagelayout']);
                $c->setContextualParameter('admin.children_limit', $scope, $config['admin']['children_limit']);
                $c->setContextualParameter('admin.related_content_limit', $scope, $config['admin']['related_content_limit']);
                $c->setContextualParameter('field.autocomplete_limit', $scope, $config['field']['autocomplete_limit']);
            }
        );

        $processor->mapConfigArray('tag_view_match', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
        $processor->mapConfigArray('edit_views', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = array(
            'netgen_tags.yml' => 'netgen_tags',
            'ezpublish.yml' => 'ezpublish',
            'platformui/yui.yml' => 'ez_platformui',
            'platformui/css.yml' => 'ez_platformui',
            'platformui/javascript.yml' => 'ez_platformui',
        );

        foreach ($configs as $fileName => $extensionName) {
            $configFile = __DIR__ . '/../Resources/config/' . $fileName;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($extensionName, $config);
            $container->addResource(new FileResource($configFile));
        }
    }
}
