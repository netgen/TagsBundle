<?php

namespace Netgen\TagsBundle\Core\REST\Input\Parser;

use EzSystems\EzPlatformRest\Exceptions;
use EzSystems\EzPlatformRest\Input\BaseParser;
use EzSystems\EzPlatformRest\Input\ParserTools;
use EzSystems\EzPlatformRest\Input\ParsingDispatcher;
use Netgen\TagsBundle\API\Repository\TagsService;

class TagSynonymCreate extends BaseParser
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \EzSystems\EzPlatformRest\Input\ParserTools
     */
    private $parserTools;

    public function __construct(TagsService $tagsService, ParserTools $parserTools)
    {
        $this->tagsService = $tagsService;
        $this->parserTools = $parserTools;
    }

    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('mainLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'mainLanguageCode' element for SynonymCreate.");
        }

        $synonymCreateStruct = $this->tagsService->newSynonymCreateStruct(
            null,
            $data['mainLanguageCode']
        );

        if (array_key_exists('remoteId', $data)) {
            $synonymCreateStruct->remoteId = $data['remoteId'];
        }

        if (array_key_exists('alwaysAvailable', $data)) {
            $synonymCreateStruct->alwaysAvailable = $this->parserTools->parseBooleanValue($data['alwaysAvailable']);
        }

        if (array_key_exists('names', $data)) {
            if (!is_array($data['names'])
                || !array_key_exists('value', $data['names'])
                || !is_array($data['names']['value'])
            ) {
                throw new Exceptions\Parser("Invalid 'names' element for SynonymCreate.");
            }

            $keywords = $this->parserTools->parseTranslatableList($data['names']);
            foreach ($keywords as $languageCode => $keyword) {
                $synonymCreateStruct->setKeyword($keyword, $languageCode);
            }
        }

        return $synonymCreateStruct;
    }
}
