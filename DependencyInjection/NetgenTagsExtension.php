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
        $loader->load('rest_services.yml');
        $loader->load('fieldtypes.yml');
        $loader->load('persistence.yml');
        $loader->load('papi.yml');
        $loader->load('default_settings.yml');
        $loader->load('templating.yml');
        $loader->load('view.yml');
        $loader->load('limitations.yml');
        $loader->load('storage_engines/legacy.yml');
        $loader->load('admin/controllers.yml');
        $loader->load('admin/templating.yml');
        $loader->load('validators.yml');
        $loader->load('param_converters.yml');

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));

        if (in_array('EzSystemsEzPlatformSolrSearchEngineBundle', $activatedBundles)) {
            $loader->load('storage_engines/solr/criterion_visitors.yml');
        }

        if (in_array('EzPublishLegacySearchEngineBundle', $activatedBundles)) {
            $loader->load('storage_engines/legacy/search_query_handlers.yml');
        }

        $processor = new ConfigurationProcessor($container, 'eztags');

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
            'ezplatform_ui.yml' => 'ez_platformui',
        );

        foreach ($configs as $fileName => $extensionName) {
            $configFile = __DIR__ . '/../Resources/config/' . $fileName;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($extensionName, $config);
            $container->addResource(new FileResource($configFile));
        }
    }
}
