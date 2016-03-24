<?php

namespace Netgen\TagsBundle\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Exceptions;
use Netgen\TagsBundle\API\Repository\TagsService;

class TagSynonymCreate extends BaseParser
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\REST\Common\Input\ParserTools $parserTools
     */
    public function __construct(TagsService $tagsService, ParserTools $parserTools)
    {
        $this->tagsService = $tagsService;
        $this->parserTools = $parserTools;
    }

    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('MainTag', $data) || !is_array($data['MainTag'])) {
            throw new Exceptions\Parser("Missing or invalid 'MainTag' element for SynonymCreate.");
        }

        if (!array_key_exists('_href', $data['MainTag'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for MainTag element in SynonymCreate.");
        }

        if (!array_key_exists('mainLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'mainLanguageCode' element for SynonymCreate.");
        }

        $tagHrefParts = explode('/', $this->requestParser->parseHref($data['MainTag']['_href'], 'tagPath'));

        $synonymCreateStruct = $this->tagsService->newSynonymCreateStruct(
            array_pop($tagHrefParts),
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
