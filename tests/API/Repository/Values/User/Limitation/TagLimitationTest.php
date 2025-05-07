<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\API\Repository\Values\User\Limitation;

use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Tests\Integration\Core\Repository\Values\User\Limitation\BaseLimitationTest;
use Netgen\TagsBundle\API\Repository\Values\User\Limitation\TagLimitation;

final class TagLimitationTest extends BaseLimitationTest
{
    public function testTagLimitationAllow(): void
    {
        $repository = $this->getRepository();

        /** @var \Netgen\TagsBundle\Tests\API\Repository\SetupFactory\Legacy $setupFactory */
        $setupFactory = $this->getSetupFactory();
        $tagsService = $setupFactory->getTagsService(false);

        /* BEGIN: Use Case */

        $user = $this->createUserVersion1();
        $roleService = $repository->getRoleService();

        $role = $roleService->createRole($roleService->newRoleCreateStruct('Tags editor'));

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'add');
        $policyCreateStruct->addLimitation(
            new TagLimitation(
                [
                    'limitationValues' => [47],
                ],
            ),
        );

        $role = $roleService->addPolicyByRoleDraft($role, $policyCreateStruct);

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'read');
        $role = $roleService->addPolicyByRoleDraft($role, $policyCreateStruct);

        $roleService->publishRoleDraft($role);
        $role = $roleService->loadRole($role->id);

        $roleService->assignRoleToUser($role, $user);
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        $tagCreateStruct = $tagsService->newTagCreateStruct(
            $tagsService->loadTag(47)->id,
            'eng-GB',
        );
        $tagCreateStruct->setKeyword('netgen', 'eng-GB');

        $createdTag = $tagsService->createTag($tagCreateStruct);

        /* END: Use Case */

        self::assertSame(
            'netgen',
            $createdTag->getKeyword('eng-GB'),
        );
    }

    public function testTagLimitationForbid(): void
    {
        $this->expectException(UnauthorizedException::class);

        $repository = $this->getRepository();

        /** @var \Netgen\TagsBundle\Tests\API\Repository\SetupFactory\Legacy $setupFactory */
        $setupFactory = $this->getSetupFactory();
        $tagsService = $setupFactory->getTagsService(false);

        /* BEGIN: Use Case */

        $user = $this->createUserVersion1();
        $roleService = $repository->getRoleService();

        $role = $roleService->createRole($roleService->newRoleCreateStruct('Tags editor'));

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'add');
        $policyCreateStruct->addLimitation(
            new TagLimitation(
                [
                    'limitationValues' => [47, 48],
                ],
            ),
        );
        $role = $roleService->addPolicyByRoleDraft($role, $policyCreateStruct);

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'read');
        $role = $roleService->addPolicyByRoleDraft($role, $policyCreateStruct);

        $roleService->publishRoleDraft($role);
        $role = $roleService->loadRole($role->id);

        $roleService->assignRoleToUser($role, $user);
        $repository->getPermissionResolver()->setCurrentUserReference($user);

        $tagCreateStruct = $tagsService->newTagCreateStruct(
            $tagsService->loadTag(50)->id,
            'eng-GB',
        );
        $tagCreateStruct->setKeyword('netgen', 'eng-GB');

        $tagsService->createTag($tagCreateStruct);

        /* END: Use Case */
    }
}
