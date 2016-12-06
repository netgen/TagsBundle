<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\LanguageService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationListType extends AbstractType
{
    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $languageService;

    /**
     * LanguageSelectType constructor.
     *
     * @param \eZ\Publish\API\Repository\LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'translation_domain' => 'eztags_admin',
                    'tag' => null,
                    'choices' => function (Options $options) {
                        $choices = array();

                        foreach ($options['languages'] as $language) {
                            $choices += array(
                                $this->languageService->loadLanguage($language)->name => $language,
                            );
                        }

                        return $choices;
                    },
                    'choices_as_values' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => false,
                    'data' => function (Options $options) {
                        if (!isset($options['languages'][0])) {
                            return null;
                        }

                        return $options['languages'][0];
                    },
                    'preferred_choices' => function (Options $options) {
                        if ($options['tag'] !== null) {
                            return $options['tag']->languageCodes;
                        }

                        return array();
                    },
                )
            )
            ->setRequired(
                array(
                    'languages',
                    'tag',
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }
}
