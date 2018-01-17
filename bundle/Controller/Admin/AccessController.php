<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccessController extends Controller
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Returns ez:tags:add flag for "Add new tag" button visibility
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAddPermissionFlagAction()
    {
        return new JsonResponse($this->authorizationChecker->isGranted('ez:tags:add'));

    }
}