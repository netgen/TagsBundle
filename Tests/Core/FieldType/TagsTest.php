<?php

namespace EzSystems\TagsBundle\Tests\Core\FieldType;

use eZ\Publish\Core\FieldType\Tests\FieldTypeTest;
use EzSystems\TagsBundle\Core\FieldType\Tags\Type as TagsType;
use EzSystems\TagsBundle\Core\FieldType\Tags\Value as TagsValue;
use EzSystems\TagsBundle\API\Repository\Values\Tags\Tag;
use DateTime;
use stdClass;

/**
 * Test for eztags field type
 *
 * @group fieldType
 * @group eztags
 */
class TagsTest extends FieldTypeTest
{
    /**
     * Returns the field type under test.
     *
     * @return \EzSystems\TagsBundle\Core\FieldType\Tags\Type
     */
    protected function createFieldTypeUnderTest()
    {
        return new TagsType();
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation()
    {
        return array();
    }

    /**
     * Returns the settings schema expected from the field type.
     *
     * @return array
     */
    protected function getSettingsSchemaExpectation()
    {
        return array();
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \EzSystems\TagsBundle\Core\FieldType\Tags\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new TagsValue();
    }

    /**
     * Data provider for invalid input to acceptValue().
     *
     * @return array
     */
    public function provideInvalidInputForAcceptValue()
    {
        return array(
            array(
                42,
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException"
            ),
            array(
                "invalid",
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException"
            ),
            array(
                array(
                    new stdClass()
                ),
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException"
            ),
            array(
                new stdClass(),
                "eZ\\Publish\\Core\\Base\\Exceptions\\InvalidArgumentException"
            )
        );
    }

    /**
     * Data provider for valid input to acceptValue().
     *
     * @return array
     */
    public function provideValidInputForAcceptValue()
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
                array( new Tag() ),
                new TagsValue( array( new Tag() ) )
            ),
            array(
                new TagsValue(),
                new TagsValue()
            ),
            array(
                new TagsValue( null ),
                new TagsValue()
            ),
            array(
                new TagsValue( array() ),
                new TagsValue()
            ),
            array(
                new TagsValue( array( new Tag() ) ),
                new TagsValue( array( new Tag() ) )
            )
        );
    }

    /**
     * Provide input for the toHash() method
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        $modificationDate = new Datetime();
        $modificationDate->setTimestamp( 1234567 );

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
                        new Tag(
                            array(
                                "id" => 42,
                                "parentTagId" => 21,
                                "mainTagId" => 0,
                                "keyword" => "Croatia",
                                "depth" => 3,
                                "pathString" => "/1/21/42/",
                                "modificationDate" => $modificationDate,
                                "remoteId" => "123456abcdef"
                            )
                        )
                    )
                ),
                array(
                    array(
                        "id" => 42,
                        "parent_id" => 21,
                        "main_tag_id" => 0,
                        "keyword" => "Croatia",
                        "depth" => 3,
                        "path_string" => "/1/21/42/",
                        "modified" => 1234567,
                        "remote_id" => "123456abcdef"
                    )
                )
            )
        );
    }

    /**
     * Provide input to fromHash() method
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        $modificationDate = new Datetime();
        $modificationDate->setTimestamp( 1234567 );

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
                    array(
                        "id" => 42,
                        "parent_id" => 21,
                        "main_tag_id" => 0,
                        "keyword" => "Croatia",
                        "depth" => 3,
                        "path_string" => "/1/21/42/",
                        "modified" => 1234567,
                        "remote_id" => "123456abcdef"
                    )
                ),
                new TagsValue(
                    array(
                        new Tag(
                            array(
                                "id" => 42,
                                "parentTagId" => 21,
                                "mainTagId" => 0,
                                "keyword" => "Croatia",
                                "depth" => 3,
                                "pathString" => "/1/21/42/",
                                "modificationDate" => $modificationDate,
                                "remoteId" => "123456abcdef"
                            )
                        )
                    )
                )
            )
        );
    }
}
