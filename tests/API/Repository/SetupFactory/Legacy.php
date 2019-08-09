<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\API\Repository\SetupFactory;

use eZ\Publish\API\Repository\Tests\SetupFactory\Legacy as BaseLegacy;
use eZ\Publish\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use eZ\Publish\Core\Base\ServiceContainer;
use Netgen\TagsBundle\API\Repository\TagsService as APITagsService;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\ExceptionConversion;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\Core\Repository\TagsMapper;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\DependencyInjection\Compiler\DefaultStorageEnginePass;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
final class Legacy extends BaseLegacy
{
    /**
     * Initial data for eztags field type.
     *
     * @var array
     */
    private static $tagsInitialData;

    public function getServiceContainer(): ServiceContainer
    {
        /** @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader $loader */
        $loader = null;

        if (!isset(self::$serviceContainer)) {
            $config = include __DIR__ . '/../../../../vendor/ezsystems/ezpublish-kernel/config.php';
            $installDir = $config['install_dir'];

            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
            $containerBuilder = include $config['container_builder_path'];

            $loader->load('search_engines/legacy.yml');
            $loader->load('tests/integration_legacy.yml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/papi.yml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/limitations.yml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/fieldtypes.yml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/persistence.yml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/storage/doctrine.yml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/storage/cache_disabled.yml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/search/legacy.yml');

            $loader->load(__DIR__ . '/../../../../tests/settings/settings.yml');
            $loader->load(__DIR__ . '/../../../../tests/settings/integration/legacy.yml');

            $containerBuilder->setParameter(
                'legacy_dsn',
                self::$dsn
            );

            $containerBuilder->addCompilerPass(new FieldRegistryPass());
            $containerBuilder->addCompilerPass(new DefaultStorageEnginePass());

            self::$serviceContainer = new ServiceContainer(
                $containerBuilder,
                $installDir,
                $config['cache_dir'],
                true,
                true
            );
        }

        return self::$serviceContainer;
    }

    /**
     * Returns a configured tags service for testing.
     */
    public function getTagsService(bool $initializeFromScratch = true): APITagsService
    {
        $repository = $this->getRepository($initializeFromScratch);

        /** @var \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler */
        $languageHandler = $this->getServiceContainer()->get('eztags.ezpublish.spi.persistence.legacy.language.handler');

        /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator */
        $languageMaskGenerator = $this->getServiceContainer()->get('eztags.ezpublish.persistence.legacy.language.mask_generator');

        $tagsHandler = new Handler(
            new ExceptionConversion(
                new DoctrineDatabase(
                    $this->getDatabaseHandler(),
                    $languageHandler,
                    $languageMaskGenerator
                )
            ),
            new Mapper(
                $languageHandler,
                $languageMaskGenerator
            )
        );

        return new TagsService(
            $repository,
            $tagsHandler,
            new TagsMapper($languageHandler)
        );
    }

    protected function getPostInsertStatements(): array
    {
        $statements = parent::getPostInsertStatements();

        if (self::$db === 'pgsql') {
            $setvalPath = __DIR__ . '/../../../_fixtures/schema/setval.pgsql.sql';

            /** @var array $queries */
            $queries = preg_split('(;\\s*$)m', (string) file_get_contents($setvalPath));

            return array_merge($statements, array_filter($queries));
        }

        return $statements;
    }

    protected function getInitialData(): array
    {
        parent::getInitialData();

        if (!isset(self::$tagsInitialData)) {
            self::$tagsInitialData = include __DIR__ . '/../../../_fixtures/tags_tree.php';
            self::$initialData = array_merge(self::$initialData, self::$tagsInitialData);
        }

        return self::$initialData;
    }

    protected function initializeSchema(): void
    {
        parent::initializeSchema();

        $statements = $this->getTagsSchemaStatements();
        $this->applyStatements($statements);
    }

    /**
     * Returns the database schema as an array of SQL statements.
     */
    private function getTagsSchemaStatements(): array
    {
        $tagsSchemaPath = __DIR__ . '/../../../_fixtures/schema/schema.' . self::$db . '.sql';

        /** @var array $queries */
        $queries = preg_split('(;\\s*$)m', (string) file_get_contents($tagsSchemaPath));

        return array_filter($queries);
    }
}
