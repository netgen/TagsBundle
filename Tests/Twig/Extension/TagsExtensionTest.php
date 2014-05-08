<?php

namespace Netgen\TagsBundle\Tests\Twig\Extension;

use Netgen\TagsBundle\Twig\Extension\TagsExtension;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy\Utils;
use eZ\Publish\Core\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use PHPUnit_Framework_TestCase;

class TagsExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \Netgen\TagsBundle\Twig\Extension\TagsExtension
     */
    protected $tagsExtension;

    /**
     * Sets up the test
     */
    public function setUp()
    {
        parent::setUp();
        $this->doSetUp();
    }

    /**
     * Test for eztags_get_url Twig function
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param string $expectedUrl
     *
     * @dataProvider getTagUrlTestData
     */
    public function testGetTagUrl( Tag $tag, $expectedUrl )
    {
        $this->assertEquals(
            $expectedUrl,
            $this->tagsExtension->getTagUrl( $tag )
        );
    }

    /**
     * Provides test data for self::testGetTagUrl test
     *
     * @return array
     */
    public function getTagUrlTestData()
    {
        $this->doSetUp();

        return array(
            array(
                $this->tagsService->loadTag( 58 ),
                "event"
            ),
            array(
                $this->tagsService->loadTag( 79 ),
                "guest+post"
            ),
            array(
                $this->tagsService->loadTag( 86 ),
                "ez+publish/eZ+Publish+5"
            ),
            array(
                $this->tagsService->loadTag( 62 ),
                "ez+publish/template/template+operators"
            ),
        );
    }

    /**
     * Sets up the tests
     */
    protected function doSetUp()
    {
        if ( $this->tagsService === null || $this->tagsExtension === null )
        {
            Utils::getRepository()->setCurrentUser( $this->getStubbedUser( 14 ) );
            $this->tagsService = Utils::getTagsService();
            $this->tagsExtension = new TagsExtension( $this->tagsService );
        }
    }

    /**
     * Returns User stub with $id as User/Content id
     *
     * @param int $id
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     */
    protected function getStubbedUser( $id )
    {
        return new User(
            array(
                "content" => new Content(
                    array(
                        "versionInfo" => new VersionInfo(
                            array(
                                "contentInfo" => new ContentInfo( array( "id" => $id ) )
                            )
                        ),
                        "internalFields" => array()
                    )
                )
            )
        );
    }
}
