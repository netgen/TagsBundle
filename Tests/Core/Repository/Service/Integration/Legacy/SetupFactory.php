<?php

namespace Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy;

use eZ\Publish\Core\Base\ServiceContainer;
use Netgen\TagsBundle\Tests\API\Repository\SetupFactory\Legacy as APILegacySetupFactory;

use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\ExceptionConversion;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class SetupFactory extends APILegacySetupFactory
{
    /**
     * Returns the service container used for initialization of the repository
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    protected function getServiceContainer()
    {
        if ( !isset( static::$serviceContainer ) )
        {
            $config = include __DIR__ . '/../../../../../../vendor/ezsystems/ezpublish-kernel/config.php';
            $installDir = $config['install_dir'];

            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
            $containerBuilder = include $config['container_builder_path'];

            /** @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader $loader */
            $loader->load( 'tests/integration_legacy_core.yml' );
            $loader->load( __DIR__ . '/../../../../../../Tests/settings/settings.yml' );

            $containerBuilder->setParameter(
                'legacy_dsn',
                static::$dsn
            );

            static::$serviceContainer = new ServiceContainer(
                $containerBuilder,
                $installDir,
                $config['cache_dir'],
                true,
                true
            );
        }

        return static::$serviceContainer;
    }

    /**
     * Returns a configured tags service for testing.
     *
     * @return \Netgen\TagsBundle\API\Repository\TagsService
     */
    public function getTagsService()
    {
        $repository = $this->getRepository( true );

        $tagsHandler = new Handler(
            new ExceptionConversion(
                new DoctrineDatabase(
                    $this->getDatabaseHandler()
                )
            ),
            new Mapper()
        );

        return new TagsService( $repository, $tagsHandler );
    }
}
