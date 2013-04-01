<?php

namespace EzSystems\TagsBundle\Tests\API\Repository\FieldType;

use eZ\Publish\API\Repository\Tests\FieldType\BaseIntegrationTest;
use eZ\Publish\API\Repository\Values\Content\Field;
use EzSystems\TagsBundle\API\Repository\Values\Tags\Tag;
use EzSystems\TagsBundle\Core\FieldType\Tags\Value as TagsValue;
use DateTime;
use stdClass;

/**
 * Integration test for eztags field type
 *
 * @group integration
 * @group field-type
 */
class TagsIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field type
     *
     * @return string
     */
    public function getTypeName()
    {
        return "eztags";
    }

    /**
     * Get expected settings schema
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array();
    }

    /**
     * Get a valid $fieldSettings value
     *
     * @return array
     */
    public function getValidFieldSettings()
    {
        return array();
    }

    /**
     * Get $fieldSettings value not accepted by the field type
     *
     * @return array
     */
    public function getInvalidFieldSettings()
    {
        return array(
            "unknown" => 42,
        );
    }

    /**
     * Get expected validator schema
     *
     * @return array
     */
    public function getValidatorSchema()
    {
        return array();
    }

    /**
     * Get a valid $validatorConfiguration
     *
     * @return array
     */
    public function getValidValidatorConfiguration()
    {
        return array();
    }

    /**
     * Get $validatorConfiguration not accepted by the field type
     *
     * @return array
     */
    public function getInvalidValidatorConfiguration()
    {
        return array(
            "unknown" => array( "value" => 42 )
        );
    }

    /**
     * Get initial field data for valid object creation
     *
     * @return \EzSystems\TagsBundle\Core\FieldType\Tags\Value
     */
    public function getValidCreationFieldData()
    {
        return new TagsValue(
            array(
                $this->getTag1()
            )
        );
    }

    /**
     * Asserts that the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was stored and loaded correctly.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     */
    public function assertFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            "EzSystems\\TagsBundle\\Core\\FieldType\\Tags\\Value",
            $field->value
        );

        $this->assertEquals(
            array(
                $this->getTag1()
            ),
            $field->value->tags
        );
    }

    /**
     * Get field data which will result in errors during creation
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          "eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException"
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidCreationFieldData()
    {
        return array(
            array(
                42,
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType"
            ),
            array(
                "invalid",
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType"
            ),
            array(
                array(
                    new stdClass()
                ),
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType"
            ),
            array(
                new stdClass(),
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentType"
            )
        );
    }

    /**
     * Get valid field data for updating content
     *
     * @return mixed
     */
    public function getValidUpdateFieldData()
    {
        return new TagsValue(
            array(
                $this->getTag1(),
                $this->getTag2()
            )
        );
    }

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidUpdateFieldData()}
     * was stored and loaded correctly.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     */
    public function assertUpdatedFieldDataLoadedCorrect( Field $field )
    {
        $this->assertInstanceOf(
            "EzSystems\\TagsBundle\\Core\\FieldType\\Tags\\Value",
            $field->value
        );

        $this->assertEquals(
            array(
                $this->getTag1(),
                $this->getTag2()
            ),
            $field->value->tags
        );
    }

    /**
     * Get field data which will result in errors during update
     *
     * This is a PHPUnit data provider.
     *
     * The returned records must contain of an error producing data value and
     * the expected exception class (from the API or SPI, not implementation
     * specific!) as the second element. For example:
     *
     * <code>
     * array(
     *      array(
     *          new DoomedValue( true ),
     *          "eZ\\Publish\\API\\Repository\\Exceptions\\ContentValidationException"
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array[]
     */
    public function provideInvalidUpdateFieldData()
    {
        return $this->provideInvalidCreationFieldData();
    }

    /**
     * Asserts the the field data was loaded correctly.
     *
     * Asserts that the data provided by {@link getValidCreationFieldData()}
     * was copied and loaded correctly.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Field $field
     */
    public function assertCopiedFieldDataLoadedCorrectly( Field $field )
    {
        $this->assertInstanceOf(
            "EzSystems\\TagsBundle\\Core\\FieldType\\Tags\\Value",
            $field->value
        );

        $this->assertEquals(
            array(
                $this->getTag1()
            ),
            $field->value->tags
        );
    }

    /**
     * Get data to test to hash method
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the original value assigned to the
     * first index and the expected hash result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          new MyValue( true ),
     *          array( "myValue" => true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    public function provideToHashData()
    {
        return array(
            array(
                new TagsValue(),
                array()
            ),
            array(
                new TagsValue( null ),
                array()
            ),
            array(
                new TagsValue( array() ),
                array()
            ),
            array(
                new TagsValue(
                    array(
                        $this->getTag1()
                    )
                ),
                array(
                    $this->getTagHash1()
                )
            )
        );
    }

    /**
     * Get hashes and their respective converted values
     *
     * This is a PHPUnit data provider
     *
     * The returned records must have the the input hash assigned to the
     * first index and the expected value result to the second. For example:
     *
     * <code>
     * array(
     *      array(
     *          array( "myValue" => true ),
     *          new MyValue( true ),
     *      ),
     *      // ...
     * );
     * </code>
     *
     * @return array
     */
    public function provideFromHashData()
    {
        return array(
            array(
                null,
                new TagsValue()
            ),
            array(
                array(),
                new TagsValue()
            ),
            array(
                array(
                    $this->getTagHash1()
                ),
                new TagsValue(
                    array(
                        $this->getTag1()
                    )
                )
            )
        );
    }

    /**
     * Provides data for testing if field value is empty
     *
     * @return array
     */
    public function providerForTestIsEmptyValue()
    {
        return array(
            array( new TagsValue() ),
            array( new TagsValue( null ) ),
            array( new TagsValue( array() ) )
        );
    }

    /**
     * Provides data for testing if field value is not empty
     *
     * @return array
     */
    public function providerForTestIsNotEmptyValue()
    {
        return array(
            array(
                $this->getValidCreationFieldData()
            )
        );
    }

    /**
     * Returns a tag for tests
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected function getTag1()
    {
        $modificationDate = new Datetime();
        $modificationDate->setTimestamp( 1308153110 );

        return new Tag(
            array(
                "id" => 40,
                "parentTagId" => 7,
                "mainTagId" => 0,
                "keyword" => "eztags",
                "depth" => 3,
                "pathString" => "/8/7/40/",
                "modificationDate" => $modificationDate,
                "remoteId" => "182be0c5cdcd5072bb1864cdee4d3d6e"
            )
        );
    }

    /**
     * Returns a tag for tests
     *
     * @return \EzSystems\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected function getTag2()
    {
        $modificationDate = new Datetime();
        $modificationDate->setTimestamp( 1343169159 );

        return new Tag(
            array(
                "id" => 8,
                "parentTagId" => 0,
                "mainTagId" => 0,
                "keyword" => "ezpublish",
                "depth" => 1,
                "pathString" => "/8/",
                "modificationDate" => $modificationDate,
                "remoteId" => "eccbc87e4b5ce2fe28308fd9f2a7baf3"
            )
        );
    }

    /**
     * Returns a hash version of tag for tests
     *
     * @return array
     */
    protected function getTagHash1()
    {
        return array(
            "id" => 40,
            "parent_id" => 7,
            "main_tag_id" => 0,
            "keyword" => "eztags",
            "depth" => 3,
            "path_string" => "/8/7/40/",
            "modified" => 1308153110,
            "remote_id" => "182be0c5cdcd5072bb1864cdee4d3d6e"
        );
    }
}
