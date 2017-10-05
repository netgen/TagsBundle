<?php

namespace Netgen\TagsBundle\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Visitor;

class ContentList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \Netgen\TagsBundle\Core\REST\Server\Values\ContentList $data
     */
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
