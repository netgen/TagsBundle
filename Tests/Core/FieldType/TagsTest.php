<?php

namespace Netgen\TagsBundle\Tests\Core\FieldType;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\FieldType\Tests\FieldTypeTest;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\FieldType\Tags\Type as TagsType;
use Netgen\TagsBundle\Core\FieldType\Tags\Value as TagsValue;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use DateTime;
use stdClass;

/**
 * Test for eztags field type.
 *
 * @group fieldType
 * @group eztags
 */
class TagsTest extends FieldTypeTest
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tagsService;

    /**
     * Returns the identifier of the field type under test.
     *
     * @return string
     */
    protected function provideFieldTypeIdentifier()
    {
        return 'eztags';
    }

    /**
     * Returns the field type under test.
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Type
     */
    protected function createFieldTypeUnderTest()
    {
        $this->tagsService = $this->getMock(TagsService::class);

        $this->tagsService->expects($this->any())
            ->method('loadTag')
            ->will($this->returnCallback(array($this, 'getTagsServiceLoadTagValues')));

        $tagsType = new TagsType($this->tagsService);
        $tagsType->setEditViews(
            array(
                'default' => array('identifier' => 'Default'),
                'select' => array('identifier' => 'Select'),
            )
        );

        return $tagsType;
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
        return array(
            'subTreeLimit' => array(
                'type' => 'int',
                'default' => 0,
            ),
            'hideRootTag' => array(
                'type' => 'boolean',
                'default' => false,
            ),
            'maxTags' => array(
                'type' => 'int',
                'default' => 0,
            ),
            'editView' => array(
                'type' => 'string',
                'default' => TagsType::EDIT_VIEW_DEFAULT_VALUE,
            ),
        );
    }

    /**
     * Returns the empty value expected from the field type.
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
     */
    protected function getEmptyValueExpectation()
    {
        return new TagsValue();
    }

    /**
     * Returns values for TagsService::loadTag based on input value.
     *
     * @param int $tagId
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\NotFoundException
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    public function getTagsServiceLoadTagValues($tagId)
    {
        if ($tagId < 0 || $tagId == PHP_INT_MAX) {
            throw new NotFoundException('tag', $tagId);
        }

        return new Tag(
            array(
                'id' => $tagId,
            )
        );
    }

    /**
     * Provide data sets with field settings which are considered valid by the
     * {@link validateFieldSettings()} method.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     *
     * @return array
     */
    public function provideValidFieldSettings()
    {
        return array(
            array(
                array(),
            ),
            array(
                array(
                    'subTreeLimit' => 0,
                ),
            ),
            array(
                array(
                    'subTreeLimit' => 5,
                ),
            ),
            array(
                array(
                    'editView' => TagsType::EDIT_VIEW_DEFAULT_VALUE,
                ),
            ),
            array(
                array(
                    'editView' => 'Select',
                ),
            ),
            array(
                array(
                    'hideRootTag' => true,
                ),
            ),
            array(
                array(
                    'hideRootTag' => false,
                ),
            ),
            array(
                array(
                    'maxTags' => 0,
                ),
            ),
            array(
                array(
                    'maxTags' => 10,
                ),
            ),
        );
    }

    /**
     * Provide data sets with field settings which are considered invalid by the
     * {@link validateFieldSettings()} method. The method must return a
     * non-empty array of validation error when receiving such field settings.
     *
     * Returns an array of data provider sets with a single argument: A valid
     * set of field settings.
     *
     * @return array
     */
    public function provideInValidFieldSettings()
    {
        return array(
            array(
                true,
            ),
            array(
                array(
                    'nonExistingKey' => 42,
                ),
            ),
            array(
                array(
                    'subTreeLimit' => true,
                ),
            ),
            array(
                array(
                    'subTreeLimit' => -5,
                ),
            ),
            array(
                array(
                    'subTreeLimit' => PHP_INT_MAX,
                ),
            ),
            array(
                array(
                    'editView' => 'Unknown',
                ),
            ),
            array(
                array(
                    'hideRootTag' => 42,
                ),
            ),
            array(
                array(
                    'maxTags' => true,
                ),
            ),
            array(
                array(
                    'maxTags' => -5,
                ),
            ),
        );
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
                InvalidArgumentException::class,
            ),
            array(
                'invalid',
                InvalidArgumentException::class,
            ),
            array(
                array(
                    new stdClass(),
                ),
                InvalidArgumentException::class,
            ),
            array(
                new stdClass(),
                InvalidArgumentException::class,
            ),
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
                new TagsValue(),
            ),
            array(
                array(),
                new TagsValue(),
            ),
            array(
                array(new Tag()),
                new TagsValue(array(new Tag())),
            ),
            array(
                new TagsValue(),
                new TagsValue(),
            ),
            array(
                new TagsValue(null),
                new TagsValue(),
            ),
            array(
                new TagsValue(array()),
                new TagsValue(),
            ),
            array(
                new TagsValue(array(new Tag())),
                new TagsValue(array(new Tag())),
            ),
        );
    }

    /**
     * Provide input for the toHash() method.
     *
     * @return array
     */
    public function provideInputForToHash()
    {
        return array(
            array(
                new TagsValue(),
                array(),
            ),
            array(
                new TagsValue(null),
                array(),
            ),
            array(
                new TagsValue(array()),
                array(),
            ),
            array(
                new TagsValue(
                    array(
                        $this->getTag(),
                    )
                ),
                array(
                    $this->getTagHash(),
                ),
            ),
        );
    }

    /**
     * Provide input to fromHash() method.
     *
     * @return array
     */
    public function provideInputForFromHash()
    {
        return array(
            array(
                null,
                new TagsValue(),
            ),
            array(
                array(),
                new TagsValue(),
            ),
            array(
                array(
                    $this->getTagHash(),
                ),
                new TagsValue(
                    array(
                        $this->getTag(),
                    )
                ),
            ),
        );
    }

    /**
     * Provides data for the getName() test.
     *
     * @return array
     */
    public function provideDataForGetName()
    {
        return array(
            array(
                new TagsValue(),
                '',
            ),
            array(
                new TagsValue(null),
                '',
            ),
            array(
                new TagsValue(array()),
                '',
            ),
            array(
                new TagsValue(
                    array(
                        $this->getTag(),
                    )
                ),
                'eztags',
            ),
            array(
                new TagsValue(
                    array(
                        $this->getTag(),
                        $this->getTag(),
                    )
                ),
                'eztags, eztags',
            ),
        );
    }

    /**
     * Returns a tag for tests.
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected function getTag()
    {
        $modificationDate = new Datetime();
        $modificationDate->setTimestamp(1308153110);

        return new Tag(
            array(
                'id' => 40,
                'parentTagId' => 7,
                'mainTagId' => 0,
                'keywords' => array('eng-GB' => 'eztags'),
                'depth' => 3,
                'pathString' => '/8/7/40/',
                'modificationDate' => $modificationDate,
                'remoteId' => '182be0c5cdcd5072bb1864cdee4d3d6e',
                'alwaysAvailable' => false,
                'mainLanguageCode' => 'eng-GB',
                'languageCodes' => array('eng-GB'),
            )
        );
    }

    /**
     * Returns a hash version of tag for tests.
     *
     * @return array
     */
    protected function getTagHash()
    {
        return array(
            'id' => 40,
            'parent_id' => 7,
            'main_tag_id' => 0,
            'keywords' => array('eng-GB' => 'eztags'),
            'depth' => 3,
            'path_string' => '/8/7/40/',
            'modified' => 1308153110,
            'remote_id' => '182be0c5cdcd5072bb1864cdee4d3d6e',
            'always_available' => false,
            'main_language_code' => 'eng-GB',
            'language_codes' => array('eng-GB'),
        );
    }
}
