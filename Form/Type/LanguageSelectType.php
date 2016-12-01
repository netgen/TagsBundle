<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\LanguageService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageSelectType extends AbstractType
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
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'translation_domain' => 'eztags_admin',
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
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();

        foreach ($options['languages'] as $language) {
            $choices += array(
                $this->languageService->loadLanguage($language)->name => $language,
            );
        }

        $builder->add('languageCode', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', array(
            'choices' => $choices,
            'choices_as_values' => true,
            'expanded' => true,
            'multiple' => false,
            'label' => false,
            'preferred_choices' => $options['tag']->languageCodes,
            'data' => $options['tag']->mainLanguageCode,
        ));
    }
}
