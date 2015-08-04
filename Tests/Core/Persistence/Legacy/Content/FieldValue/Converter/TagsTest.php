<?php

namespace Netgen\TagsBundle\Tests\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags as TagsConverter;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints;
use PHPUnit_Framework_TestCase;

/**
 * Test case for Tags converter in Legacy storage.
 */
class TagsTest extends PHPUnit_Framework_TestCase
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
     * @covers \Netgen\TagsBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\Tags::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue();
        $value->data = array('key1', 'key2');
        $value->sortKey = false;

        $storageFieldValue = new StorageFieldValue();

        $this->converter->toStorageValue($value, $storageFieldValue);

        $this->assertNull($storageFieldValue->dataText);
        $this->assertNull($storageFieldValue->dataInt);
        $this->assertNull($storageFieldValue->dataFloat);

        $this->assertEquals(0, $storageFieldValue->sortKeyInt);
        $this->assertEquals('', $storageFieldValue->sortKeyString);
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

        $this->assertNull($fieldValue->data);
        $this->assertNull($fieldValue->sortKey);
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
            array(
                'subTreeLimit' => 0,
                'showDropDown' => false,
                'hideRootTag' => true,
                'maxTags' => 10,
            )
        );

        $storageFieldDefinition = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition(
            new PersistenceFieldDefinition(
                array(
                    'fieldTypeConstraints' => $fieldTypeConstraints,
                )
            ),
            $storageFieldDefinition
        );

        self::assertEquals(0, $storageFieldDefinition->dataInt1);
        self::assertEquals(false, $storageFieldDefinition->dataInt2);
        self::assertEquals(true, $storageFieldDefinition->dataInt3);
        self::assertEquals(10, $storageFieldDefinition->dataInt4);
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
                array(
                    'dataInt1' => 0,
                    'dataInt2' => false,
                    'dataInt3' => true,
                    'dataInt4' => 10,
                )
            ),
            $fieldDefinition
        );

        self::assertInstanceOf('eZ\\Publish\\Core\\FieldType\\FieldSettings', $fieldDefinition->fieldTypeConstraints->fieldSettings);
        self::assertEquals(0, $fieldDefinition->fieldTypeConstraints->fieldSettings['subTreeLimit']);
        self::assertEquals(false, $fieldDefinition->fieldTypeConstraints->fieldSettings['showDropDown']);
        self::assertEquals(true, $fieldDefinition->fieldTypeConstraints->fieldSettings['hideRootTag']);
        self::assertEquals(10, $fieldDefinition->fieldTypeConstraints->fieldSettings['maxTags']);
        self::assertNull($fieldDefinition->defaultValue->data);
    }
}
