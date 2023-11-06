<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Validator\Constraints\Tag as TagConstraint;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

final class TagTreeType extends AbstractType
{
    public function __construct(private TagsService $tagsService) {}

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
                    'constraints' => static fn (Options $options): array => [
                        new Constraints\Type(['type' => 'int']),
                        new Constraints\NotBlank(),
                        new TagConstraint(['allowRootTag' => $options['allowRootTag']]),
                    ],
                ],
            );
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(
            new class() implements DataTransformerInterface {
                public function transform(mixed $value): mixed
                {
                    return $value;
                }

                public function reverseTransform(mixed $value): int
                {
                    return (int) $value;
                }
            },
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $tag = null;
        if ($form->getData() !== null) {
            try {
                $tag = $this->tagsService->loadTag((int) $form->getData());
            } catch (NotFoundException) {
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
