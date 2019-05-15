<?php

namespace Netgen\TagsBundle\Core\SignalSlot;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\SignalSlot\Signal;
use EzSystems\PlatformHttpCacheBundle\PurgeClient\PurgeClientInterface;
use EzSystems\PlatformHttpCacheBundle\SignalSlot\AbstractSlot;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;

abstract class AbstractPublishSlot extends AbstractSlot
{
    /**
     * @var Handler
     */
    protected $tagsHandler;

    /**
     * AbstractPublishSlot constructor.
     *
     * @param PurgeClientInterface $purgeClient
     * @param Handler $tagsHandler
     */
    public function __construct(PurgeClientInterface $purgeClient, Handler $tagsHandler)
    {
        parent::__construct($purgeClient);
        $this->tagsHandler = $tagsHandler;
    }

    /**
     * @param Signal $signal
     *
     * @throws NotFoundException
     *
     * @return array
     */
    protected function generateTags(Signal $signal)
    {
        $ngTagId = $this->getNgTagId($signal);

        $ngTag = $this->tagsHandler->load($ngTagId);

        return $this->getNgTagTags($ngTag);
    }

    /**
     * Extracts tag id from signal.
     *
     * @param Signal $signal
     *
     * @return mixed
     */
    abstract protected function getNgTagId(Signal $signal);

    /**
     * @param Tag $ngTag
     *
     * @return array
     */
    protected function getNgTagTags(Tag $ngTag)
    {
        return [
            'tag-' . $ngTag->id,
            'tag-' . $ngTag->parentTagId,
            'parent-tag-' . $ngTag->parentTagId,
        ];
    }
}
