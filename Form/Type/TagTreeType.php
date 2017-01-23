<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Validator\Constraints\Tag as TagConstraint;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
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
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('allowRootTag')
            ->setAllowedTypes('allowRootTag', 'bool')
            ->setRequired('disableSubtree')
            ->setAllowedTypes('disableSubtree', 'array')
            ->setDefaults(
                array(
                    'error_bubbling' => false,
                    'allowRootTag' => true,
                    'disableSubtree' => array(),
                    'constraints' => function (Options $options) {
                        return array(
                            new Constraints\Type(array('type' => 'numeric')),
                            new Constraints\NotBlank(),
                            new TagConstraint(array('allowRootTag' => $options['allowRootTag'])),
                        );
                    },
                )
            );
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
            'allowRootTag' => $options['allowRootTag'],
            'disableSubtree' => $options['disableSubtree'],
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
