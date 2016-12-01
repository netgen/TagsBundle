<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Form\DataMapper\TagUpdateStructDataMapper;
use Netgen\TagsBundle\Validator\Constraints\Structs\TagUpdateStruct as TagUpdateStructConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagUpdateType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('tag');
        $resolver->setAllowedTypes('tag', 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag');

        $resolver
            ->setDefaults(
                array(
                    'data_class' => 'Netgen\TagsBundle\API\Repository\Values\Tags\TagUpdateStruct',
                    'constraints' => function (Options $options) {
                        return array(
                            new TagUpdateStructConstraint(
                                array(
                                    'payload' => $options['tag'],
                                )
                            ),
                        );
                    },
                )
            );
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setDataMapper(new TagUpdateStructDataMapper($options['languageCode']));
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return 'Netgen\TagsBundle\Form\Type\TagType';
    }
}
