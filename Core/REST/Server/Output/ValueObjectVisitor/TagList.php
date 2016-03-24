<?php

namespace Netgen\TagsBundle\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

class TagList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \Netgen\TagsBundle\Core\REST\Server\Values\TagList $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
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
                    'ezpublish_rest_eztags_loadTag',
                    array('tagPath' => trim($restTag->tag->pathString, '/'))
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('Tag');
        }

        $generator->endList('Tag');

        $generator->endObjectElement('TagList');
    }
}
