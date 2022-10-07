<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Visitor;

use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;
use function trim;

class RestTag extends ValueObjectVisitor
{
    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $generator->startObjectElement('Tag');
        $visitor->setHeader('Content-Type', $generator->getMediaType('Tag'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('TagUpdate'));

        $tag = $data->tag;

        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_eztags_loadTag',
                ['tagPath' => trim($tag->pathString, '/')]
            )
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $tag->id);
        $generator->endValueElement('id');

        $tagPath = explode('/', trim($tag->pathString, '/'));
        $parentPathString = implode('/', array_slice($tagPath, 0, count($tagPath) - 1));

        if ($tag->parentTagId > 0) {
            $generator->startObjectElement('ParentTag', 'Tag');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_eztags_loadTag',
                    [
                        'tagPath' => $parentPathString,
                    ]
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('ParentTag');
        }

        if ($tag->mainTagId > 0) {
            $generator->startObjectElement('MainTag', 'Tag');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_eztags_loadTag',
                    [
                        // Main tags always have a same parent tag ID
                        'tagPath' => $parentPathString . '/' . $tag->mainTagId,
                    ]
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

        if (in_array($tag->mainTagId, [null, 0], true)) {
            $generator->startValueElement('childrenCount', $data->childrenCount);
            $generator->endValueElement('childrenCount');

            $generator->startObjectElement('Children', 'TagList');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_eztags_loadTagChildren',
                    [
                        'tagPath' => trim($tag->pathString, '/'),
                    ]
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('Children');

            $generator->startValueElement('synonymsCount', $data->synonymsCount);
            $generator->endValueElement('synonymsCount');

            $generator->startObjectElement('Synonyms', 'TagList');
            $generator->startAttribute(
                'href',
                $this->router->generate(
                    'ezpublish_rest_eztags_loadTagSynonyms',
                    ['tagPath' => trim($tag->pathString, '/')]
                )
            );
            $generator->endAttribute('href');
            $generator->endObjectElement('Synonyms');
        }

        $generator->startObjectElement('RelatedContent', 'ContentList');
        $generator->startAttribute(
            'href',
            $this->router->generate(
                'ezpublish_rest_eztags_getRelatedContent',
                [
                    'tagPath' => trim($tag->pathString, '/'),
                ]
            )
        );
        $generator->endAttribute('href');
        $generator->endObjectElement('RelatedContent');

        $generator->endObjectElement('Tag');
    }
}
