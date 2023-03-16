<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Structs\SynonymCreateStruct as SynonymCreateStructConstraint;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SynonymCreateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(
                [
                    'data_class' => SynonymCreateStruct::class,
                    'constraints' => [
                        new SynonymCreateStructConstraint(),
                    ],
                ],
            );
    }

    public function getParent(): string
    {
        return TagType::class;
    }
}
