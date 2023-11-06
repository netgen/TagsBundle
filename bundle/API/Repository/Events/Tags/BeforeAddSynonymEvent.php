<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\API\Repository\Events\Tags;

use Ibexa\Contracts\Core\Repository\Event\BeforeEvent;
use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use UnexpectedValueException;

use function sprintf;

final class BeforeAddSynonymEvent extends BeforeEvent
{
    private Tag $synonym;

    public function __construct(private SynonymCreateStruct $synonymCreateStruct) {}

    public function getSynonymCreateStruct(): SynonymCreateStruct
    {
        return $this->synonymCreateStruct;
    }

    public function getSynonym(): Tag
    {
        $this->synonym ??
            throw new UnexpectedValueException(
                sprintf(
                    'Return value is not set or not a type of %s. Check with hasSynonym() or set it with setSynonym() before you call the getter.',
                    Tag::class,
                ),
            );

        return $this->synonym;
    }

    public function setSynonym(Tag $synonym): void
    {
        $this->synonym = $synonym;
    }

    public function hasSynonym(): bool
    {
        return isset($this->synonym);
    }
}
