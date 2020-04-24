<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Generator;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function iterator_to_array;

final class TranslationListType extends AbstractType
{
    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    private $languageService;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(LanguageService $languageService, ConfigResolverInterface $configResolver)
    {
        $this->languageService = $languageService;
        $this->configResolver = $configResolver;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $languages = $this->configResolver->getParameter('languages');

        $choices = iterator_to_array(
            (function () use ($languages): Generator {
                foreach ($this->languageService->loadLanguageListByCode($languages) as $language) {
                    yield $language->name => $language->languageCode;
                }
            })()
        );

        $resolver
            ->setRequired('tag')
            ->setAllowedTypes('tag', [Tag::class, 'null'])
            ->setDefaults(
                [
                    'tag' => null,
                    'choices' => $choices,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => false,
                    'data' => static function (Options $options) use ($languages): ?string {
                        if ($options['tag'] instanceof Tag) {
                            return $options['tag']->mainLanguageCode;
                        }

                        return $languages[0] ?? null;
                    },
                    'preferred_choices' => static function (Options $options): array {
                        if ($options['tag'] instanceof Tag) {
                            return $options['tag']->languageCodes;
                        }

                        return [];
                    },
                ]
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars += [
            'tag' => $options['tag'],
        ];
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
