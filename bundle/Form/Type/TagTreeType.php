<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Validator\Constraints\Tag as TagConstraint;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class TagTreeType extends AbstractType
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('allowRootTag')
            ->setAllowedTypes('allowRootTag', 'bool')
            ->setRequired('disableSubtree')
            ->setAllowedTypes('disableSubtree', 'array')
            ->setDefaults(
                [
                    'error_bubbling' => false,
                    'allowRootTag' => true,
                    'disableSubtree' => [],
                    'constraints' => static function (Options $options) {
                        return [
                            new Constraints\Type(['type' => 'numeric']),
                            new Constraints\NotBlank(),
                            new TagConstraint(['allowRootTag' => $options['allowRootTag']]),
                        ];
                    },
                ]
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $tag = null;
        if ($form->getData() !== null) {
            try {
                $tag = $this->tagsService->loadTag($form->getData());
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        $view->vars += [
            'tag' => $tag,
            'allowRootTag' => $options['allowRootTag'],
            'disableSubtree' => $options['disableSubtree'],
        ];
    }

    public function getParent(): string
    {
        return HiddenType::class;
    }
}
