<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\LanguageService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationListType extends AbstractType
{
    /**
     * @var \eZ\Publish\API\Repository\LanguageService
     */
    protected $languageService;

    /**
     * @var array
     */
    protected $languages;

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
     * Setter method for array with languages.
     *
     * @param array|null $languages
     */
    public function setLanguages(array $languages = null)
    {
        $this->languages = $languages;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $choices = array();

        if (method_exists($this->languageService, 'loadLanguageListByCode')) {
            $choices = iterator_to_array(
                (function () {
                    foreach ($this->languageService->loadLanguageListByCode($this->languages) as $language) {
                        yield $language->name => $language->languageCode;
                    }
                })()
            );
        } else {
            foreach ($this->languages as $language) {
                $choices += array(
                    $this->languageService->loadLanguage($language)->name => $language,
                );
            }
        }

        $resolver
            ->setRequired('tag')
            ->setAllowedTypes('tag', array(Tag::class, 'null'))
            ->setDefaults(
                array(
                    'tag' => null,
                    'choices' => $choices,
                    'choices_as_values' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => false,
                    'data' => function (Options $options) {
                        if ($options['tag'] instanceof Tag) {
                            return $options['tag']->mainLanguageCode;
                        }

                        return isset($this->languages[0]) ? $this->languages[0] : null;
                    },
                    'preferred_choices' => function (Options $options) {
                        if ($options['tag'] instanceof Tag) {
                            return $options['tag']->languageCodes;
                        }

                        return array();
                    },
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars += array(
            'tag' => $options['tag'],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
