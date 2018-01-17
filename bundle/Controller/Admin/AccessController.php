<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;

class AccessController extends Controller
{
    /**
     * Returns if current user has access to tags/add policy.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function canAddTagsAction()
    {
        return new JsonResponse($this->isGranted('ez:tags:add'));
    }
}
