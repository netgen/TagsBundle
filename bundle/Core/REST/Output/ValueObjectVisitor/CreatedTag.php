<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\Visitor;

use function trim;

final class CreatedTag extends RestTag
{
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        parent::visit($visitor, $generator, $data->restTag);

        $visitor->setHeader(
            'Location',
            $this->router->generate(
                'ibexa.rest.netgen_tags_loadTag',
                [
                    'tagPath' => trim($data->restTag->tag->pathString, '/'),
                ],
            ),
        );

        $visitor->setStatus(201);
    }
}
