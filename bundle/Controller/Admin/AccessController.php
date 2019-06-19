<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;

class AccessController extends Controller
{
    /**
     * Returns if current user has access to tags/add policy.
     */
    public function canAddTagsAction(): JsonResponse
    {
        return new JsonResponse($this->isGranted('ez:tags:add'));
    }
}
