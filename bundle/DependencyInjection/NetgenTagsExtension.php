<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\DependencyInjection;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ConfigurationProcessor;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class NetgenTagsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));

        if (!in_array('EzCoreExtraBundle', $activatedBundles, true)) {
            throw new RuntimeException('Netgen Tags Bundle requires EzCoreExtraBundle (lolautruche/ez-core-extra-bundle) to be activated to work properly.');
        }

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
        $loader->load('storage/doctrine.yml');
        $loader->load('admin/controllers.yml');
        $loader->load('admin/templating.yml');
        $loader->load('forms.yml');
        $loader->load('validators.yml');
        $loader->load('param_converters.yml');
        $loader->load('installer.yml');
        $loader->load('search/related_content.yml');
        $loader->load('ezadminui/default_settings.yml');
        $loader->load('ezadminui/services.yml');

        $persistenceCache = 'disabled';
        if ($container->getParameter('eztags.enable_persistence_cache')) {
            $persistenceCache = 'psr6';
        }

        $loader->load('storage/cache_' . $persistenceCache . '.yml');

        if (in_array('EzSystemsEzPlatformSolrSearchEngineBundle', $activatedBundles, true)) {
            $loader->load('search/solr.yml');
        }

        if (in_array('EzPublishLegacySearchEngineBundle', $activatedBundles, true)) {
            $loader->load('search/legacy.yml');
        }

        $this->processSemanticConfig($container, $config);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = [
            'netgen_tags.yml' => 'netgen_tags',
            'ezplatform.yml' => 'ezpublish',
            'framework/twig.yml' => 'twig',
            'ezadminui/twig.yml' => 'twig',
        ];

        foreach ($configs as $fileName => $extensionName) {
            $configFile = __DIR__ . '/../Resources/config/' . $fileName;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($extensionName, $config);
            $container->addResource(new FileResource($configFile));
        }
    }

    /**
     * Processes semantic config and translates it to container parameters.
     */
    private function processSemanticConfig(ContainerBuilder $container, array $config): void
    {
        $processor = new ConfigurationProcessor($container, 'eztags');
        $processor->mapConfig(
            $config,
            static function ($config, $scope, ContextualizerInterface $c): void {
                $c->setContextualParameter('tag_view.cache', $scope, $config['tag_view']['cache']);
                $c->setContextualParameter('tag_view.ttl_cache', $scope, $config['tag_view']['ttl_cache']);
                $c->setContextualParameter('tag_view.default_ttl', $scope, $config['tag_view']['default_ttl']);
                $c->setContextualParameter('tag_view.template', $scope, $config['tag_view']['template']);
                $c->setContextualParameter('tag_view.pagelayout', $scope, $config['tag_view']['pagelayout']);
                $c->setContextualParameter('tag_view.path_prefix', $scope, $config['tag_view']['path_prefix']);

                $c->setContextualParameter('tag_view.related_content_list.limit', $scope, $config['tag_view']['related_content_list']['limit']);
                $c->setContextualParameter('tag_view.related_content_list.return_content_info', $scope, $config['tag_view']['related_content_list']['return_content_info']);

                $c->setContextualParameter('admin.pagelayout', $scope, $config['admin']['pagelayout']);
                $c->setContextualParameter('admin.children_limit', $scope, $config['admin']['children_limit']);
                $c->setContextualParameter('admin.related_content_limit', $scope, $config['admin']['related_content_limit']);
                $c->setContextualParameter('field.autocomplete_limit', $scope, $config['field']['autocomplete_limit']);
            }
        );

        $processor->mapConfigArray('tag_view_match', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
        $processor->mapConfigArray('edit_views', $config, ContextualizerInterface::MERGE_FROM_SECOND_LEVEL);
    }
}
