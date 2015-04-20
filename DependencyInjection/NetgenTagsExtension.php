<?php

namespace Netgen\TagsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages the bundle configuration
 */
class NetgenTagsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load( array $configs, ContainerBuilder $container )
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration( $configuration, $configs );

        $loader = new YamlFileLoader( $container, new FileLocator( __DIR__ . "/../Resources/config" ) );

        $loader->load( "services.yml" );
        $loader->load( "fieldtypes.yml" );
        $loader->load( "persistence.yml" );
        $loader->load( "storage_engines/legacy.yml" );
        $loader->load( "papi.yml" );
        $loader->load( "default_settings.yml" );
        $loader->load( "roles.yml" );
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prepend( ContainerBuilder $container )
    {
        $configFile = __DIR__ . '/../Resources/config/ezpublish.yml';
        $config = Yaml::parse( file_get_contents( $configFile ) );
        $container->prependExtensionConfig( 'ezpublish', $config );
        $container->addResource( new FileResource( $configFile ) );
    }
}
