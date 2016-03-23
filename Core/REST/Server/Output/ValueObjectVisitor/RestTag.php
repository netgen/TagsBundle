<?php

namespace Netgen\TagsBundle\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\Core\REST\Server\Values\RestContent as RestContentValue;

class RestTag extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param \Netgen\TagsBundle\Core\REST\Server\Values\RestTag $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Tag');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Tag'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('TagUpdate'));

        $tag = $data->tag;

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'eztags_rest_loadTag',
                array('tagPath' => trim($tag->pathString, '/'))
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $tag->id);
        $generator->endValueElement('id');

        $tagPath = explode('/', trim($tag->pathString, '/'));
        $parentPathString = implode('/', array_slice($tagPath, 0, count($tagPath) - 1));

        if (!empty($tag->parentTagId)) {
            $generator->startObjectElement('ParentTag', 'Tag');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'eztags_rest_loadTag',
                    array(
                        'tagPath' => $parentPathString,
                    )
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('ParentTag');
        }

        if (!empty($tag->mainTagId)) {
            $generator->startObjectElement('MainTag', 'Tag');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'eztags_rest_loadTag',
                    array(
                        // Main tags always have a same parent tag ID
                        'tagPath' => $parentPathString . '/' . $tag->mainTagId,
                    )
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('MainTag');
        }

        $this->visitTranslatedList($generator, $tag->keywords, 'keywords');

        $generator->startValueElement('depth', $tag->depth);
        $generator->endValueElement('depth');

        $generator->startValueElement('pathString', $tag->pathString);
        $generator->endValueElement('pathString');

        $generator->startValueElement(
            'modificationDate',
            $tag->modificationDate->format('c')
        );
        $generator->endValueElement('modificationDate');

        $generator->startValueElement('remoteId', $tag->remoteId);
        $generator->endValueElement('remoteId');

        $generator->startValueElement(
            'alwaysAvailable',
            $this->serializeBool($generator, $tag->alwaysAvailable)
        );
        $generator->endValueElement('alwaysAvailable');

        $generator->startValueElement('mainLanguageCode', $tag->mainLanguageCode);
        $generator->endValueElement('mainLanguageCode');

        $generator->startValueElement(
            'languageCodes',
            implode(',', $tag->languageCodes)
        );
        $generator->endValueElement('languageCodes');

        $generator->startValueElement('childCount', $data->childCount);
        $generator->endValueElement('childCount');

        $generator->startObjectElement('Children', 'TagList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'eztags_rest_loadTagChildren',
                array(
                    'tagPath' => trim($tag->pathString, '/'),
                )
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('Children');

        $generator->endObjectElement('Tag');
    }
}
