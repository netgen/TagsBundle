<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use Netgen\TagsBundle\Core\FieldType\Tags\Type;
use Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags as TagsConverter;
use PHPUnit\Framework\TestCase;

/**
 * Test case for Tags converter in Legacy storage.
 */
class TagsTest extends TestCase
{
    /**
     * Tags converter.
     *
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new TagsConverter();
    }

    /**
     * @group fieldType
     * @group eztags
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::create
     */
    public function testCreate()
    {
        $converter = TagsConverter::create();
        self::assertInstanceOf(get_class($this->converter), $converter);
    }

    /**
     * @group fieldType
     * @group eztags
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = ['key1', 'key2'];
        $value->sortKey = false;

        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);

        self::assertNull($storageFieldValue->dataText);
        self::assertNull($storageFieldValue->dataInt);
        self::assertNull($storageFieldValue->dataFloat);

        self::assertEquals(0, $storageFieldValue->sortKeyInt);
        self::assertEquals('', $storageFieldValue->sortKeyString);
    }

    /**
     * @group fieldType
     * @group eztags
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $fieldValue = new FieldValue();

        $this->converter->toFieldValue($storageFieldValue, $fieldValue);

        self::assertNull($fieldValue->data);
        self::assertNull($fieldValue->sortKey);
    }

    /**
     * @group fieldType
     * @group eztags
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $fieldTypeConstraints = new FieldTypeConstraints();
        $fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'hideRootTag' => true,
                'editView' => 'Select',
            ]
        );

        $fieldTypeConstraints->validators = [
            'TagsValueValidator' => [
                'subTreeLimit' => 0,
                'maxTags' => 10,
            ],
        ];

        $storageFieldDefinition = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition(
            new PersistenceFieldDefinition(
                [
                    'fieldTypeConstraints' => $fieldTypeConstraints,
                ]
            ),
            $storageFieldDefinition
        );

        self::assertEquals(0, $storageFieldDefinition->dataInt1);
        self::assertEquals(1, $storageFieldDefinition->dataInt3);
        self::assertEquals(10, $storageFieldDefinition->dataInt4);
        self::assertEquals('Select', $storageFieldDefinition->dataText1);
    }

    /**
     * @group fieldType
     * @group eztags
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinitionWithNoSettingsAndValidators()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition(
            new PersistenceFieldDefinition(),
            $storageFieldDefinition
        );

        self::assertEquals(0, $storageFieldDefinition->dataInt1);
        self::assertEquals(0, $storageFieldDefinition->dataInt3);
        self::assertEquals(0, $storageFieldDefinition->dataInt4);
        self::assertEquals(Type::EDIT_VIEW_DEFAULT_VALUE, $storageFieldDefinition->dataText1);
    }

    /**
     * @group fieldType
     * @group eztags
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $fieldDefinition = new PersistenceFieldDefinition();

        $this->converter->toFieldDefinition(
            new StorageFieldDefinition(
                [
                    'dataInt1' => 0,
                    'dataInt3' => true,
                    'dataInt4' => 10,
                    'dataText1' => 'Select',
                ]
            ),
            $fieldDefinition
        );

        self::assertInstanceOf(FieldSettings::class, $fieldDefinition->fieldTypeConstraints->fieldSettings);
        self::assertEquals(0, $fieldDefinition->fieldTypeConstraints->validators['TagsValueValidator']['subTreeLimit']);
        self::assertEquals(10, $fieldDefinition->fieldTypeConstraints->validators['TagsValueValidator']['maxTags']);
        self::assertTrue($fieldDefinition->fieldTypeConstraints->fieldSettings['hideRootTag']);
        self::assertEquals('Select', $fieldDefinition->fieldTypeConstraints->fieldSettings['editView']);
        self::assertNull($fieldDefinition->defaultValue->data);
    }

    /**
     * @group fieldType
     * @group eztags
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::getIndexColumn
     */
    public function testGetIndexColumn()
    {
        $indexColumn = $this->converter->getIndexColumn();
        self::assertFalse($indexColumn);
    }
}
