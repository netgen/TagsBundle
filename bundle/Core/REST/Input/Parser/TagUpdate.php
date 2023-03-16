<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Input\Parser;

use Ibexa\Contracts\Rest\Exceptions;
use Ibexa\Contracts\Rest\Input\ParsingDispatcher;
use Ibexa\Rest\Input\BaseParser;
use Ibexa\Rest\Input\ParserTools;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;

use function array_key_exists;
use function is_array;

final class TagUpdate extends BaseParser
{
    public function __construct(private TagsService $tagsService, private ParserTools $parserTools)
    {
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher): TagUpdateStruct
    {
        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();

        if (array_key_exists('mainLanguageCode', $data)) {
            $tagUpdateStruct->mainLanguageCode = $data['mainLanguageCode'];
        }

        if (array_key_exists('remoteId', $data)) {
            $tagUpdateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $tagUpdateStruct->alwaysAvailable = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names']) || !array_key_exists('value', $data['names']) || !is_array($data['names']['value'])) {
                throw new Exceptions\Parser("Invalid 'names' element for TagUpdate.");
            }

            $keywords = $this->parserTools->parseTranslatableList($data['names']);
            foreach ($keywords as $languageCode => $keyword) {
                $tagUpdateStruct->setKeyword($keyword, $languageCode);
            }
        }

        return $tagUpdateStruct;
    }
}
