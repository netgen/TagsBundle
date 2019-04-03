<?php

namespace Netgen\TagsBundle\Tests\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Tests\Values\User\Limitation\BaseLimitationTest;
use Netgen\TagsBundle\API\Repository\Values\User\Limitation\TagLimitation;

class TagLimitationTest extends BaseLimitationTest
{
    /**
     * Test for the TagLimitation that allows access.
     */
    public function testTagLimitationAllow()
    {
        $repository = $this->getRepository();

        /** @var \Netgen\TagsBundle\Tests\API\Repository\SetupFactory\Legacy $setupFactory */
        $setupFactory = $this->getSetupFactory();
        $tagsService = $setupFactory->getTagsService(false);

        /* BEGIN: Use Case */

        $user = $this->createUserVersion1();
        $roleService = $repository->getRoleService();

        $role = $roleService->createRole($roleService->newRoleCreateStruct('Tags editor'));
        $roleService->publishRoleDraft($role);
        $role = $roleService->loadRole($role->id);

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'add');
        $policyCreateStruct->addLimitation(
            new TagLimitation(
                [
                    'limitationValues' => [47],
                ]
            )
        );

        $role = $roleService->addPolicy($role, $policyCreateStruct);

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'read');
        $role = $roleService->addPolicy($role, $policyCreateStruct);

        $roleService->assignRoleToUser($role, $user);
        $repository->setCurrentUser($user);

        $tagCreateStruct = $tagsService->newTagCreateStruct(
            $tagsService->loadTag(47)->id,
            'eng-GB'
        );
        $tagCreateStruct->setKeyword('netgen', 'eng-GB');

        $createdTag = $tagsService->createTag($tagCreateStruct);

        /* END: Use Case */

        self::assertSame(
            'netgen',
            $createdTag->getKeyword('eng-GB')
        );
    }

    /**
     * Test for the TagLimitation that forbids access.
     *
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testTagLimitationForbid()
    {
        $repository = $this->getRepository();

        /** @var \Netgen\TagsBundle\Tests\API\Repository\SetupFactory\Legacy $setupFactory */
        $setupFactory = $this->getSetupFactory();
        $tagsService = $setupFactory->getTagsService(false);

        /* BEGIN: Use Case */

        $user = $this->createUserVersion1();
        $roleService = $repository->getRoleService();

        $role = $roleService->createRole($roleService->newRoleCreateStruct('Tags editor'));
        $roleService->publishRoleDraft($role);
        $role = $roleService->loadRole($role->id);

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'add');
        $policyCreateStruct->addLimitation(
            new TagLimitation(
                [
                    'limitationValues' => [47, 48],
                ]
            )
        );
        $role = $roleService->addPolicy($role, $policyCreateStruct);

        $policyCreateStruct = $roleService->newPolicyCreateStruct('tags', 'read');
        $role = $roleService->addPolicy($role, $policyCreateStruct);

        $roleService->assignRoleToUser($role, $user);
        $repository->setCurrentUser($user);

        $tagCreateStruct = $tagsService->newTagCreateStruct(
            $tagsService->loadTag(50)->id,
            'eng-GB'
        );
        $tagCreateStruct->setKeyword('netgen', 'eng-GB');

        $tagsService->createTag($tagCreateStruct);

        /* END: Use Case */
    }
}
