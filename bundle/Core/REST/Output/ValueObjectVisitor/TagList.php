<?php

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Visitor;

class TagList extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \EzSystems\EzPlatformRest\Output\Visitor $visitor
     * @param \EzSystems\EzPlatformRest\Output\Generator $generator
     * @param \Netgen\TagsBundle\Core\REST\Values\TagList $data
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
                    ['tagPath' => trim($restTag->tag->pathString, '/')]
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('Tag');
        }

        $generator->endList('Tag');

        $generator->endObjectElement('TagList');
    }
}
