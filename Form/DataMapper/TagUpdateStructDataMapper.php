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

    /**
     * TagStructDataMapper constructor.
     *
     * @param string $languageCode
     */
    public function __construct($languageCode)
    {
        $this->languageCode = $languageCode;
    }

    /**
     * Maps properties of some data to a list of forms.
     *
     * @param mixed $data Structured data
     * @param \Symfony\Component\Form\FormInterface[] $forms A list of {@link FormInterface} instances
     *
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException if the type of the data parameter is not supported
     */
    public function mapDataToForms($data, $forms)
    {
        if (!$data instanceof TagUpdateStruct) {
            return;
        }

        $forms = iterator_to_array($forms);

        $forms['keyword']->setData($data->getKeyword($this->languageCode));
        $forms['alwaysAvailable']->setData($data->alwaysAvailable);
        $forms['remoteId']->setData($data->remoteId);
    }

    /**
     * Maps the data of a list of forms into the properties of some data.
     *
     * @param \Symfony\Component\Form\FormInterface[] $forms A list of {@link FormInterface} instances
     * @param mixed $data Structured data
     *
     * @throws \Symfony\Component\Form\Exception\UnexpectedTypeException if the type of the data parameter is not supported
     */
    public function mapFormsToData($forms, &$data)
    {
        if (!$data instanceof TagUpdateStruct) {
            return;
        }

        $forms = iterator_to_array($forms);

        $data->setKeyword($forms['keyword']->getData(), $this->languageCode);
        $data->alwaysAvailable = $forms['alwaysAvailable']->getData();
        $data->remoteId = $forms['remoteId']->getData();
    }
}
