<?php

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\Visitor;

class CreatedTag extends RestTag
{
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
