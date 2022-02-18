<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\API\Repository\SetupFactory;

use Doctrine\DBAL\Connection;
use Ibexa\Contracts\Core\Test\Persistence\Fixture;
use Ibexa\Contracts\Core\Test\Persistence\Fixture\FixtureImporter;
use Ibexa\Contracts\Core\Test\Persistence\Fixture\PhpArrayFileFixture;
use Ibexa\Contracts\Core\Test\Repository\SetupFactory\Legacy as BaseLegacy;
use Ibexa\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use Ibexa\Core\Base\ServiceContainer;
use Netgen\TagsBundle\API\Repository\TagsService as APITagsService;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\ExceptionConversion;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\Core\Repository\TagsMapper;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\DependencyInjection\Compiler\DefaultStorageEnginePass;
use function array_filter;
use function file_get_contents;
use function preg_split;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
final class Legacy extends BaseLegacy
{
    /**
     * Initial data for eztags field type.
     *
     * @var \Ibexa\Contracts\Core\Test\Persistence\Fixture|null
     */
    private static $tagsInitialData;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function getServiceContainer(): ServiceContainer
    {
        /** @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader $loader */
        $loader = null;

        if (!isset(self::$serviceContainer)) {
            $config = include __DIR__ . '/../../../../vendor/ezsystems/ezplatform-kernel/config.php';
            $installDir = $config['install_dir'];

            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
            $containerBuilder = include $config['container_builder_path'];

            // eZ Publish kernel config
            $loader->load('search_engines/legacy.yml');
            $loader->load('tests/integration_legacy.yml');

            // Netgen Tags config
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/papi.yaml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/limitations.yaml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/fieldtypes.yaml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/persistence.yaml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/storage/doctrine.yaml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/storage/cache_disabled.yaml');
            $loader->load(__DIR__ . '/../../../../bundle/Resources/config/search/legacy.yaml');

            $loader->load(__DIR__ . '/../../../../tests/settings/settings.yaml');
            $loader->load(__DIR__ . '/../../../../tests/settings/integration/legacy.yaml');

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

        /** @var \Ibexa\Contracts\Core\Persistence\Content\Language\Handler $languageHandler */
        $languageHandler = $this->getServiceContainer()->get('eztags.ezpublish.spi.persistence.legacy.language.handler');

        /** @var \Ibexa\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator */
        $languageMaskGenerator = $this->getServiceContainer()->get('eztags.ezpublish.persistence.legacy.language.mask_generator');

        $tagsHandler = new Handler(
            new ExceptionConversion(
                new DoctrineDatabase(
                    $this->getDatabaseConnection(),
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

    public function insertData(): void
    {
        parent::insertData();

        $connection = $this->getDatabaseConnection();

        $fixtureImporter = new FixtureImporter($connection);
        $fixtureImporter->import($this->getInitialTagsDataFixture());

        $this->execPostInsertStatements();
    }

    protected function execPostInsertStatements(): void
    {
        if (self::$db !== 'pgsql') {
            return;
        }

        $setValPath = __DIR__ . '/../../../_fixtures/schema/setval.postgresql.sql';

        /** @var array $queries */
        $queries = preg_split('(;\\s*$)m', (string) file_get_contents($setValPath));

        foreach ($queries as $query) {
            $this->getDatabaseConnection()->exec($query);
        }
    }

    protected function getInitialTagsDataFixture(): Fixture
    {
        if (!isset(self::$tagsInitialData)) {
            self::$tagsInitialData = new PhpArrayFileFixture(
                __DIR__ . '/../../../_fixtures/tags_tree.php'
            );
        }

        return self::$tagsInitialData;
    }

    protected function initializeSchema(): void
    {
        parent::initializeSchema();

        $statements = $this->getTagsSchemaStatements();

        foreach ($statements as $statement) {
            $this->getDatabaseConnection()->exec($statement);
        }
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

    private function getDatabaseConnection(): Connection
    {
        if (null === $this->connection) {
            /** @var \Doctrine\DBAL\Connection $connection */
            $connection = $this->getServiceContainer()->get('ezpublish.persistence.connection');
            $this->connection = $connection;
        }

        return $this->connection;
    }
}
