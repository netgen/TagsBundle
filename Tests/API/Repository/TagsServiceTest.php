<?php

namespace Netgen\TagsBundle\Tests\API\Repository;

/**
 * Test case for Tags Service using Legacy storage class.
 */
class TagsServiceTest extends BaseTagsServiceTest
{
    public function setUp()
    {
        $this->repository = $this->getRepository();
        $this->repository->setCurrentUser($this->getStubbedUser(14));
        $this->tagsService = $this->getTagsService();
    }

    /**
     * @param bool $initialInitializeFromScratch
     *
     * @return \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected function getTagsService($initialInitializeFromScratch = true)
    {
        if (null === $this->tagsService) {
            $this->tagsService = $this->getSetupFactory()->getTagsService($initialInitializeFromScratch);
        }

        return $this->tagsService;
    }
}
