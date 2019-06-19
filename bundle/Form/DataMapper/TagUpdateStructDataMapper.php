<?php

namespace Netgen\TagsBundle\Form\DataMapper;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Symfony\Component\Form\DataMapperInterface;

class TagUpdateStructDataMapper implements DataMapperInterface
{
    /**
     * @var string
     */
    protected $languageCode;

    public function __construct(string $languageCode)
    {
        $this->languageCode = $languageCode;
    }

    public function mapDataToForms($viewData, $forms): void
    {
        if (!$viewData instanceof TagUpdateStruct) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['keyword']->setData($viewData->getKeyword($this->languageCode));
        $forms['alwaysAvailable']->setData($viewData->alwaysAvailable);
        $forms['remoteId']->setData($viewData->remoteId);
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        if (!$viewData instanceof TagUpdateStruct) {
            return;
        }

        $forms = iterator_to_array($forms);

        $keyword = $forms['keyword']->getData();

        $keyword !== null ?
            $viewData->setKeyword($keyword, $this->languageCode) :
            $viewData->removeKeyword($this->languageCode);

        $viewData->alwaysAvailable = $forms['alwaysAvailable']->getData();
        $viewData->remoteId = $forms['remoteId']->getData();
    }
}
