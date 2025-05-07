<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\HttpCache\ResponseTagger;

use Ibexa\Contracts\HttpCache\ResponseTagger\ResponseTagger;
use Netgen\TagsBundle\Core\HttpCache\Tagger;
use Netgen\TagsBundle\View\TagView;

final readonly class TagViewTagger implements ResponseTagger
{
    public function __construct(
        private Tagger $tagger,
    ) {}

    public function tag($value): void
    {
        if (!$value instanceof TagView) {
            return;
        }

        $this->tagger->tagByTag($value->getTag());
    }
}
