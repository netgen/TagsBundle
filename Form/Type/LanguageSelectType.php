<?php

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageSelectType extends AbstractType
{
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'languageCode',
            'Netgen\TagsBundle\Form\Type\TranslationListType',
            array(
                'languages' => $options['languages'],
                'tag' => $options['tag'],
            )
        );
    }
}
