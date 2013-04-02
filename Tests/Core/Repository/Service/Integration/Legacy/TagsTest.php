<?php

namespace EzSystems\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy;

use EzSystems\TagsBundle\Tests\Core\Repository\Service\Integration\TagsBase as BaseTagsServiceTest;
use EzSystems\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy\Utils;
use Exception;

/**
 * Test case for Tags Service using Legacy storage class
 */
class TagsTest extends BaseTagsServiceTest
{
    public function setUp()
    {
        $this->repository = $this->getRepository();
        $this->repository->setCurrentUser( $this->getStubbedUser( 14 ) );
        $this->tagsService = $this->getTagsService();
    }

    /**
     * @return \eZ\Publish\API\Repository\Repository
     */
    protected function getRepository()
    {
        try
        {
            return Utils::getRepository();
        }
        catch ( Exception $e )
        {
            $this->markTestIncomplete( $e->getMessage() );
        }
    }

    /**
     * @return \EzSystems\TagsBundle\API\Repository\TagsService
     */
    protected function getTagsService()
    {
        try
        {
            return Utils::getTagsService();
        }
        catch ( Exception $e )
        {
            $this->markTestIncomplete( $e->getMessage() );
        }
    }
}
