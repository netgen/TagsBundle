<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\DataMapper;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct;
use Symfony\Component\Form\DataMapperInterface;
use Traversable;

use function iterator_to_array;

final class TagUpdateStructDataMapper implements DataMapperInterface
{
    public function __construct(private string $languageCode)
    {
    }

    public function mapDataToForms(mixed $viewData, Traversable $forms): void
    {
        if (!$viewData instanceof TagUpdateStruct) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['keyword']->setData($viewData->getKeyword($this->languageCode));
        $forms['alwaysAvailable']->setData($viewData->alwaysAvailable);
        $forms['remoteId']->setData($viewData->remoteId);
    }

    public function mapFormsToData(Traversable $forms, mixed &$viewData): void
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
