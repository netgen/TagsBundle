<?php

namespace Netgen\TagsBundle;

use eZ\Bundle\EzPublishLegacyBundle\LegacyBundles\LegacyBundleInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NetgenTagsBundle extends Bundle implements LegacyBundleInterface
{
    public function getLegacyExtensionsNames()
    {
        return array('eztags');
    }
}
