<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;

final class AccessController extends Controller
{
    /**
     * Returns if current user has access to tags/add policy.
     */
    public function canAddTagsAction(): JsonResponse
    {
        return new JsonResponse($this->isGranted('ez:tags:add'));
    }
}
