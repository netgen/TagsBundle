<?php

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

        if (in_array('eZPlatformUIBundle', $activatedBundles, true)) {
            $loader->load('platformui/default_settings.yml');
            $loader->load('platformui/services.yml');
        }

        if (in_array('EzPlatformAdminUiBundle', $activatedBundles, true)) {
            $loader->load('ezadminui/default_settings.yml');
            $loader->load('ezadminui/services.yml');
        }

        if (in_array('EzSystemsEzPlatformSolrSearchEngineBundle', $activatedBundles, true)) {
            $loader->load('search/solr.yml');
        }

        if (in_array('EzPublishLegacySearchEngineBundle', $activatedBundles, true)) {
            $loader->load('search/legacy.yml');
        }

        $this->processSemanticConfig($container, $config);
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
            'ezplatform.yml' => 'ezpublish',
            'framework/twig.yml' => 'twig',
        );

        $activatedBundles = array_keys($container->getParameter('kernel.bundles'));

        if (in_array('eZPlatformUIBundle', $activatedBundles, true)) {
            $configs['platformui/yui.yml'] = 'ez_platformui';
            $configs['platformui/css.yml'] = 'ez_platformui';
            $configs['platformui/javascript.yml'] = 'ez_platformui';
        }

        if (in_array('EzPlatformAdminUiBundle', $activatedBundles, true)) {
            $configs['ezadminui/twig.yml'] = 'twig';
        }

        foreach ($configs as $fileName => $extensionName) {
            $configFile = __DIR__ . '/../Resources/config/' . $fileName;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($extensionName, $config);
            $container->addResource(new FileResource($configFile));
        }
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
