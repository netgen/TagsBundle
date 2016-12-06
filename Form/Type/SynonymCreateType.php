<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\Validator\Constraints\Structs\SynonymCreateStruct as SynonymCreateStructConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SynonymCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class' => 'Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct',
                    'constraints' => array(
                        new SynonymCreateStructConstraint(),
                    ),
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Netgen\TagsBundle\Form\Type\TagType';
    }
}
