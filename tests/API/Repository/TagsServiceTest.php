<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\API\Repository;

use Netgen\TagsBundle\API\Repository\TagsService;

/**
 * Test case for Tags Service using Legacy storage class.
 */
final class TagsServiceTest extends BaseTagsServiceTest
{
    protected function setUp(): void
    {
        $this->repository = $this->getRepository();
        $this->repository->getPermissionResolver()->setCurrentUserReference($this->getStubbedUser(14));
        $this->tagsService = $this->getTagsService();
    }

    private function getTagsService(bool $initialInitializeFromScratch = true): TagsService
    {
        /** @var \Netgen\TagsBundle\Tests\API\Repository\SetupFactory\Legacy $setUpFactory */
        $setUpFactory = $this->getSetupFactory();
        $this->tagsService = $this->tagsService ?? $setUpFactory->getTagsService($initialInitializeFromScratch);

        return $this->tagsService;
    }
}
