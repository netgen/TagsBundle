<?php

namespace Netgen\TagsBundle\Tests\Core\SignalSlot;

use EzSystems\PlatformHttpCacheBundle\Tests\SignalSlot\AbstractSlotTest;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Handler;
use Netgen\TagsBundle\SPI\Persistence\Tags\Tag;
use PHPUnit_Framework_MockObject_MockObject;

abstract class AbstractPublishSlotTest extends AbstractSlotTest
{
    /** @var int */
    protected $tagId = 42;
    /** @var int */
    protected $parentTagId = 42;

    /** @var Handler|PHPUnit_Framework_MockObject_MockObject */
    protected $spiTagsHandlerMock;

    /**
     * @dataProvider getUnreceivedSignals
     *
     * @param mixed $signal
     */
    public function testDoesNotReceiveOtherSignals($signal)
    {
        $this->purgeClientMock->expects(self::never())->method('purge');
        $this->purgeClientMock->expects(self::never())->method('purgeAll');

        $this->spiTagsHandlerMock->expects(self::never())->method('load');

        $this->slot->receive($signal);
    }

    /**
     * @dataProvider getReceivedSignals
     *
     * @param mixed $signal
     */
    public function testReceivePurgesCacheForTags($signal)
    {
        $this->spiTagsHandlerMock
            ->expects(self::once())
            ->method('load')
            ->with($this->tagId)
            ->willReturn(
                new Tag(
                    [
                        'id' => $this->tagId,
                        'parentTagId' => $this->parentTagId,
                    ]
                )
            );

        $this->purgeClientMock->expects(self::once())->method('purge')->with($this->generateTags());
        $this->purgeClientMock->expects(self::never())->method('purgeAll');
        parent::receive($signal);
    }

    /**
     * {@inheritdoc}
     */
    public function generateTags()
    {
        return [
            'tag-' . $this->tagId,
            'tag-' . $this->parentTagId,
            'parent-tag-' . $this->parentTagId,
        ];
    }

    /**
     * @return mixed
     */
    protected function createSlot()
    {
        $class = $this->getSlotClass();
        if ($this->spiTagsHandlerMock === null) {
            $this->spiTagsHandlerMock = $this->createMock(Handler::class);
        }

        return new $class($this->purgeClientMock, $this->spiTagsHandlerMock);
    }
}
