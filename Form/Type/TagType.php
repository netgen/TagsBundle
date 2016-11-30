<?php

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TagType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'eztags_admin',
            ])
            ->setRequired(['languageCode']);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('keyword', TextType::class, ['label' => 'tag.tag_name'])
            ->add('alwaysAvailable', CheckboxType::class, ['label' => 'tag.form.always_available', 'required' => false])
            ->add('remoteId', TextType::class, ['required' => false]);
    }
}
