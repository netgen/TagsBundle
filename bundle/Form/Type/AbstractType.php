<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\AbstractType as BaseAbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType extends BaseAbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'eztags_admin');
    }
}
