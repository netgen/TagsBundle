<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use UnexpectedValueException;
use function sprintf;

final class BeforeConvertToSynonymEvent extends BeforeEvent
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $tag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    private $mainTag;

    /**
     * @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null
     */
    private $synonym;

    public function __construct(Tag $tag, Tag $mainTag)
    {
        $this->tag = $tag;
        $this->mainTag = $mainTag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getMainTag(): Tag
    {
        return $this->mainTag;
    }

    public function getSynonym(): Tag
    {
        if ($this->synonym === null) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check with hasSynonym() or set it with setSynonym() before you call the getter.', Tag::class));
        }

        return $this->synonym;
    }

    public function setSynonym(?Tag $synonym): void
    {
        $this->synonym = $synonym;
    }

    public function hasSynonym(): bool
    {
        return $this->synonym instanceof Tag;
    }
}
