<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\HttpCache\EventSubscriber;

use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Netgen\TagsBundle\API\Repository\Events\Tags\AddSynonymEvent;
use Netgen\TagsBundle\API\Repository\Events\Tags\ConvertToSynonymEvent;
use Netgen\TagsBundle\API\Repository\Events\Tags\CopySubtreeEvent;
use Netgen\TagsBundle\API\Repository\Events\Tags\CreateTagEvent;
use Netgen\TagsBundle\API\Repository\Events\Tags\DeleteTagEvent;
use Netgen\TagsBundle\API\Repository\Events\Tags\MergeTagsEvent;
use Netgen\TagsBundle\API\Repository\Events\Tags\MoveSubtreeEvent;
use Netgen\TagsBundle\API\Repository\Events\Tags\UpdateTagEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\HttpCache\Tagger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CachePurgeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Tagger $tagger,
        private PurgeClientInterface $purgeClient,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AddSynonymEvent::class => 'onAddSynonymEvent',
            ConvertToSynonymEvent::class => 'onConvertToSynonymEvent',
            CopySubtreeEvent::class => 'onCopySubtreeEvent',
            CreateTagEvent::class => 'onCreateTagEvent',
            DeleteTagEvent::class => 'onDeleteTagEvent',
            MergeTagsEvent::class => 'onMergeTagsEvent',
            MoveSubtreeEvent::class => 'onMergeSubtreeEvent',
            UpdateTagEvent::class => 'onUpdateTagEvent',
        ];
    }

    public function onAddSynonymEvent(AddSynonymEvent $event): void
    {
        $this->purgeTags($event->getSynonym());
    }

    public function onConvertToSynonymEvent(ConvertToSynonymEvent $event): void
    {
        $this->purgeTags($event->getSynonym(), $event->getMainTag());
    }

    public function onCopySubtreeEvent(CopySubtreeEvent $event): void
    {
        $this->purgeTags($event->getCopiedTag());
    }

    public function onCreateTagEvent(CreateTagEvent $event): void
    {
        $this->purgeTags($event->getTag());
    }

    public function onDeleteTagEvent(DeleteTagEvent $event): void
    {
        $this->purgeTags($event->getTag());
    }

    public function onMergeTagsEvent(MergeTagsEvent $event): void
    {
        $this->purgeTags($event->getTag(), $event->getTargetTag());
    }

    public function onMergeSubtreeEvent(MoveSubtreeEvent $event): void
    {
        $this->purgeTags($event->getTag(), $event->getParentTag());
    }

    public function onUpdateTagEvent(UpdateTagEvent $event): void
    {
        $this->purgeTags($event->getTag());
    }

    private function purgeTags(?Tag ...$tags): void
    {
        $purgeTagsGrouped = [[]];

        foreach ($tags as $tag) {
            if ($tag === null) {
                continue;
            }

            $purgeTagsGrouped[] = $this->tagger->resolveTags($tag);
        }

        $this->purgeClient->purge(array_unique(array_merge(...$purgeTagsGrouped)));
    }
}
