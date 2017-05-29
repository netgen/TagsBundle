<?php

namespace Netgen\TagsBundle\PlatformUI\EventListener;

use Symfony\Component\HttpFoundation\Request;

abstract class PlatformUIListener
{
    /**
     * Returns if provided request is a Platform UI request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    protected function isPlatformUIRequest(Request $request)
    {
        if (stripos($request->attributes->get('_route'), 'netgen_tags_admin') !== 0) {
            return false;
        }

        return $request->headers->has('X-AJAX-Update') || $request->isXmlHttpRequest();
    }
}
