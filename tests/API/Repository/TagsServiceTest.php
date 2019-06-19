<?php

namespace Netgen\TagsBundle\Tests\API\Repository;

use Netgen\TagsBundle\API\Repository\TagsService;

/**
 * Test case for Tags Service using Legacy storage class.
 */
class TagsServiceTest extends BaseTagsServiceTest
{
    protected function setUp(): void
    {
        $this->repository = $this->getRepository();
        $this->repository->setCurrentUser($this->getStubbedUser(14));
        $this->tagsService = $this->getTagsService();
    }

    protected function getTagsService(bool $initialInitializeFromScratch = true): TagsService
    {
        $this->tagsService = $this->tagsService ?? $this->getSetupFactory()->getTagsService($initialInitializeFromScratch);

        return $this->tagsService;
    }
}
