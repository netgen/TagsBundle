<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\SPI\FieldType;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\SPI\Tests\FieldType\BaseIntegrationTest;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway\DoctrineStorage as TagsDoctrineStorage;
use Netgen\TagsBundle\Core\FieldType\Tags\Type as TagsType;
use Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags as TagsConverter;
use Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\LanguageHandlerMock;

final class TagsIntegrationTest extends BaseIntegrationTest
{
    /**
     * Property indicating whether the DB already has been set up.
     *
     * @var bool
     */
    private static $tagsSetUp = false;

    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit\Framework\MockObject\MockObject
     */
    private $tagsService;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$tagsSetUp) {
            $schema = __DIR__ . '/../../_fixtures/schema/schema.' . $this->handler->getName() . '.sql';

            $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($schema)));
            foreach ($queries as $query) {
                $this->handler->exec($query);
            }

            $this->insertDatabaseFixture(__DIR__ . '/../../_fixtures/tags_tree.php');
            self::$tagsSetUp = $this->handler;
        }
    }

    public function resetSequences(): void
    {
        parent::resetSequences();

        if ($this->handler->getName() === 'pgsql') {
            // Update PostgreSQL sequences
            $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents(__DIR__ . '/../../_fixtures/schema/setval.pgsql.sql')));
            foreach ($queries as $query) {
                $this->handler->exec($query);
            }
        }
    }

    public function getTypeName(): string
    {
        return 'eztags';
    }

    public function getCustomHandler(): Handler
    {
        $this->tagsService = $this->createMock(TagsService::class);

        $fieldType = new TagsType($this->tagsService);

        $fieldType->setTransformationProcessor($this->getTransformationProcessor());
        $fieldType->setEditViews(
            [
                'default' => ['identifier' => 'Default'],
                'select' => ['identifier' => 'Select'],
            ]
        );

        return $this->getHandler(
            'eztags',
            $fieldType,
            new TagsConverter(),
            new TagsStorage(
                new TagsDoctrineStorage(
                    $this->handler->getConnection(),
                    (new LanguageHandlerMock())($this)
                ),
                $this->tagsService
            )
        );
    }

    public function getTypeConstraints(): FieldTypeConstraints
    {
        return new FieldTypeConstraints();
    }

    public function getFieldDefinitionData(): array
    {
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'hideRootTag' => false,
                'editView' => TagsType::EDIT_VIEW_DEFAULT_VALUE,
            ]
        );

        $fieldTypeConstraints->validators = [
            'TagsValueValidator' => [
                'subTreeLimit' => 0,
                'maxTags' => 0,
            ],
        ];

        return [
            // The eztags field type does not have any special field definition properties
            ['fieldType', 'eztags'],
            ['fieldTypeConstraints', $fieldTypeConstraints],
        ];
    }

    public function getInitialValue(): FieldValue
    {
        return new FieldValue(
            [
                'data' => null,
                'externalData' => [
                    $this->getTagHash1(),
                ],
                'sortKey' => null,
            ]
        );
    }

    public function assertLoadedFieldDataCorrect(Field $field): void
    {
        self::assertSame(
            $this->getInitialValue()->externalData,
            $field->value->externalData
        );

        self::assertNull($field->value->data);
        self::assertNull($field->value->sortKey);
    }

    public function getUpdatedValue(): FieldValue
    {
        return new FieldValue(
            [
                'data' => null,
                'externalData' => [
                    $this->getTagHash1(),
                    $this->getTagHash2(),
                ],
                'sortKey' => null,
            ]
        );
    }

    public function assertUpdatedFieldDataCorrect(Field $field): void
    {
        self::assertSame(
            $this->getUpdatedValue()->externalData,
            $field->value->externalData
        );

        self::assertNull($field->value->data);
        self::assertNull($field->value->sortKey);
    }

    /**
     * Returns a hash version of tag for tests.
     */
    private function getTagHash1(): array
    {
        return [
            'id' => 40,
            'parent_id' => 7,
            'main_tag_id' => 0,
            'keywords' => ['eng-GB' => 'eztags'],
            'depth' => 3,
            'path_string' => '/8/7/40/',
            'modified' => 1308153110,
            'remote_id' => '182be0c5cdcd5072bb1864cdee4d3d6e',
            'always_available' => false,
            'main_language_code' => 'eng-GB',
            'language_codes' => ['eng-GB'],
        ];
    }

    /**
     * Returns a hash version of tag for tests.
     */
    private function getTagHash2(): array
    {
        return [
            'id' => 8,
            'parent_id' => 0,
            'main_tag_id' => 0,
            'keywords' => ['eng-GB' => 'ez publish'],
            'depth' => 1,
            'path_string' => '/8/',
            'modified' => 1343169159,
            'remote_id' => 'eccbc87e4b5ce2fe28308fd9f2a7baf3',
            'always_available' => false,
            'main_language_code' => 'eng-GB',
            'language_codes' => ['eng-GB'],
        ];
    }
}
