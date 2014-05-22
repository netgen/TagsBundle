<?php

namespace Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\Legacy;

use Netgen\TagsBundle\Tests\Core\Repository\Service\Integration\TagsBase as BaseTagsServiceTest;

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
        return Utils::getRepository();
    }

    /**
     * @return \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected function getTagsService()
    {
        return Utils::getTagsService();
    }
}
