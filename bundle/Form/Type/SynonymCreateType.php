<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Structs\SynonymCreateStruct as SynonymCreateStructConstraint;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SynonymCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(
                [
                    'data_class' => SynonymCreateStruct::class,
                    'constraints' => [
                        new SynonymCreateStructConstraint(),
                    ],
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TagType::class;
    }
}
