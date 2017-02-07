<?php

namespace Netgen\TagsBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller as BaseController;

abstract class Controller extends BaseController
{
    /**
     * Returns the Netgen Tags service.
     *
     * @return \Netgen\TagsBundle\API\Repository\TagsService
     */
    public function getTagsService()
    {
        return $this->get('eztags.api.service.tags');
    }
}
