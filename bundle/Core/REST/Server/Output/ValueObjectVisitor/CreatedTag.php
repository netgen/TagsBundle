<?php

namespace Netgen\TagsBundle\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;

class CreatedTag extends RestTag
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \Netgen\TagsBundle\Core\REST\Server\Values\CreatedTag $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        parent::visit($visitor, $generator, $data->restTag);

        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ezpublish_rest_eztags_loadTag',
                [
                    'tagPath' => trim($data->restTag->tag->pathString, '/'),
                ]
            )
        );

        $visitor->setStatus(201);
    }
}
