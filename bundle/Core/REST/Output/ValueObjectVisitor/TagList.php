<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;

use function trim;

final class TagList extends ValueObjectVisitor
{
    public function visit(Visitor $visitor, Generator $generator, mixed $data): void
    {
        $generator->startObjectElement('TagList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('TagList'));

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('Tag');

        foreach ($data->tags as $restTag) {
            $generator->startObjectElement('Tag');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ibexa.rest.netgen_tags_loadTag',
                    ['tagPath' => trim($restTag->tag->pathString, '/')],
                ),
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('Tag');
        }

        $generator->endList('Tag');

        $generator->endObjectElement('TagList');
    }
}
