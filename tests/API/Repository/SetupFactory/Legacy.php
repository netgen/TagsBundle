<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\API\Repository\SetupFactory;

use Doctrine\DBAL\Connection;
use Ibexa\Bundle\Core\DependencyInjection\Security\PolicyProvider\PoliciesConfigBuilder;
use Ibexa\Contracts\Core\Test\Persistence\Fixture;
use Ibexa\Contracts\Core\Test\Persistence\Fixture\FixtureImporter;
use Ibexa\Contracts\Core\Test\Persistence\Fixture\PhpArrayFileFixture;
use Ibexa\Contracts\Core\Test\Repository\SetupFactory\Legacy as BaseLegacy;
use Ibexa\Core\Base\Container\Compiler\Search\FieldRegistryPass;
use Netgen\TagsBundle\API\Repository\TagsService as APITagsService;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\DoctrineDatabase;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway\ExceptionConversion;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Mapper;
use Netgen\TagsBundle\Core\Repository\TagsMapper;
use Netgen\TagsBundle\Core\Repository\TagsService;
use Netgen\TagsBundle\DependencyInjection\Compiler\DefaultStorageEnginePass;
use Netgen\TagsBundle\DependencyInjection\Security\PolicyProvider\TagsPolicyProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
     */
    private static ?Fixture $tagsInitialData;

    private Connection $connection;

    /**
     * Returns a configured tags service for testing.
     */
    public function getTagsService(bool $initializeFromScratch = true): APITagsService
    {
        $repository = $this->getRepository($initializeFromScratch);

        /** @var \Ibexa\Contracts\Core\Persistence\Content\Language\Handler $languageHandler */
        $languageHandler = $this->getServiceContainer()->get('netgen_tags.ibexa.spi.persistence.legacy.language.handler');

        /** @var \Ibexa\Core\Persistence\Legacy\Content\Language\MaskGenerator $languageMaskGenerator */
        $languageMaskGenerator = $this->getServiceContainer()->get('netgen_tags.ibexa.persistence.legacy.language.mask_generator');

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

    protected function externalBuildContainer(ContainerBuilder $containerBuilder): void
    {
        $loader = new YamlFileLoader(
            $containerBuilder,
            new FileLocator(__DIR__ . '/../../../../'),
        );

        // Netgen Tags config
        $loader->load('bundle/Resources/config/papi.yaml');
        $loader->load('bundle/Resources/config/limitations.yaml');
        $loader->load('bundle/Resources/config/fieldtypes.yaml');
        $loader->load('bundle/Resources/config/persistence.yaml');
        $loader->load('bundle/Resources/config/storage/doctrine.yaml');
        $loader->load('bundle/Resources/config/storage/cache_disabled.yaml');
        $loader->load('bundle/Resources/config/search/legacy.yaml');

        $loader->load('tests/settings/settings.yaml');
        $loader->load('tests/settings/integration/legacy.yaml');

        $containerBuilder->addCompilerPass(new FieldRegistryPass());
        $containerBuilder->addCompilerPass(new DefaultStorageEnginePass());

        $policiesBuilder = new PoliciesConfigBuilder($containerBuilder);
        $tagsPolicyProvider = new TagsPolicyProvider();
        $tagsPolicyProvider->addPolicies($policiesBuilder);
    }

    protected function execPostInsertStatements(): void
    {
        if (self::$db !== 'pgsql') {
            return;
        }

        $setValPath = __DIR__ . '/../../../_fixtures/schema/setval.postgresql.sql';

        /** @var string[] $queries */
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

        /** @var string[] $queries */
        $queries = preg_split('(;\\s*$)m', (string) file_get_contents($tagsSchemaPath));

        return array_filter($queries);
    }

    private function getDatabaseConnection(): Connection
    {
        if (!isset($this->connection)) {
            /** @var \Doctrine\DBAL\Connection $connection */
            $connection = $this->getServiceContainer()->get('ibexa.persistence.connection');
            $this->connection = $connection;
        }

        return $this->connection;
    }
}
