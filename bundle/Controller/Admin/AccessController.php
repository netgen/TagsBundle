<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\JsonResponse;

class AccessController extends Controller
{
    /**
     * Returns ez:tags:add flag for "Add new tag" button visibility
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAddPermissionFlagAction()
    {
        return new JsonResponse($this->isGranted('ez:tags:add'));
    }

}