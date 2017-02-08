<?php

namespace Netgen\TagsBundle\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParserTools;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use Netgen\TagsBundle\API\Repository\TagsService;

class TagCreate extends BaseParser
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
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        if (!array_key_exists('ParentTag', $data) || !is_array($data['ParentTag'])) {
            throw new Exceptions\Parser("Missing or invalid 'ParentTag' element for TagCreate.");
        }

        if (!array_key_exists('_href', $data['ParentTag'])) {
            throw new Exceptions\Parser("Missing '_href' attribute for ParentTag element in TagCreate.");
        }

        if (!array_key_exists('mainLanguageCode', $data)) {
            throw new Exceptions\Parser("Missing 'mainLanguageCode' element for TagCreate.");
        }

        $tagHrefParts = explode('/', $this->requestParser->parseHref($data['ParentTag']['_href'], 'tagPath'));

        $tagCreateStruct = $this->tagsService->newTagCreateStruct(
            array_pop($tagHrefParts),
            $data['mainLanguageCode']
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
