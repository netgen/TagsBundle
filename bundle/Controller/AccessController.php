<?php

namespace Netgen\TagsBundle\Controller;

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
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        return new JsonResponse($this->container->get('security.authorization_checker')->isGranted('ez:tags:add'));
    }
}