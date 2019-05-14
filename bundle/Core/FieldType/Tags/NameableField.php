<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\Nameable;
use eZ\Publish\SPI\FieldType\Value;

class NameableField implements Nameable
{
    public function getFieldName(Value $value, FieldDefinition $fieldDefinition, $languageCode)
    {
        return (string) $value;
    }
}
