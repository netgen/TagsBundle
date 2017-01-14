<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class TagTreeType extends AbstractType
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $tag = null;
        if ($form->getData() !== null) {
            try {
                $tag = $this->tagsService->loadTag($form->getData());
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        $view->vars += array(
            'tag' => $tag,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return HiddenType::class;
    }
}
