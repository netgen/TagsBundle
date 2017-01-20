<?php

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType extends BaseAbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('translation_domain', 'eztags_admin');
    }
}
