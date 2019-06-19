<?php

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Visitor;

class ContentList extends ValueObjectVisitor
{
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('ContentList');
        $visitor->setHeader('Content-Type', $generator->getMediaType('ContentList'));
        //@todo Needs refactoring, disabling certain headers should not be done this way
        $visitor->setHeader('Accept-Patch', false);

        $generator->startAttribute('href', $data->path);
        $generator->endAttribute('href');

        $generator->startList('ContentInfo');
        foreach ($data->contents as $content) {
            $visitor->visitValueObject($content);
        }
        $generator->endList('ContentInfo');

        $generator->endObjectElement('ContentList');
    }
}
