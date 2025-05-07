<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;

use function array_key_exists;
use function array_pop;
use function explode;
use function is_array;

final class TagCreate extends BaseParser
{
    public function __construct(private TagsService $tagsService, private ParserTools $parserTools) {}

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): TagCreateStruct
    {
        if (!array_key_exists('ParentTag', $data) || !is_array($data['ParentTag'])) {
            throw new Exceptions\Parser("Missing or invalid 'ParentTag' element for TagCreate.");
        }

        $data['ParentTag']['_href']
            ?? throw new Exceptions\Parser("Missing '_href' attribute for ParentTag element in TagCreate.");

        $data['mainLanguageCode']
            ?? throw new Exceptions\Parser("Missing 'mainLanguageCode' element for TagCreate.");

        $tagHrefParts = explode('/', $this->requestParser->parseHref($data['ParentTag']['_href'], 'tagPath'));

        $tagCreateStruct = $this->tagsService->newTagCreateStruct(
            (int) array_pop($tagHrefParts),
            $data['mainLanguageCode'],
        );

        if (array_key_exists('remoteId', $data)) {
            $tagCreateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $tagCreateStruct->alwaysAvailable = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names'])
                || !array_key_exists('value', $data['names'])
                || !is_array($data['names']['value'])
            ) {
                throw new Exceptions\Parser("Invalid 'names' element for TagCreate.");
            }

            $keywords = $this->parserTools->parseTranslatableList($data['names']);
            foreach ($keywords as $languageCode => $keyword) {
                $tagCreateStruct->setKeyword($keyword, $languageCode);
            }
        }

        return $tagCreateStruct;
    }
}
