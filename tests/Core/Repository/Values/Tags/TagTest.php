<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Core\Repository\Values\Tags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    /**
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::getProperties
     */
    public function testObjectProperties(): void
    {
        $object = new Tag();
        $properties = $object->attributes();
        self::assertContains('id', $properties, 'Property not found');
        self::assertContains('parentTagId', $properties, 'Property not found');
        self::assertContains('mainTagId', $properties, 'Property not found');
        self::assertContains('keywords', $properties, 'Property not found');
        self::assertContains('depth', $properties, 'Property not found');
        self::assertContains('pathString', $properties, 'Property not found');
        self::assertContains('modificationDate', $properties, 'Property not found');
        self::assertContains('remoteId', $properties, 'Property not found');
        self::assertContains('mainLanguageCode', $properties, 'Property not found');
        self::assertContains('alwaysAvailable', $properties, 'Property not found');
        self::assertContains('languageCodes', $properties, 'Property not found');

        // check for duplicates and double check existence of property
        $propertiesHash = [];
        foreach ($properties as $property) {
            if (isset($propertiesHash[$property])) {
                self::fail("Property \"{$property}\" exists several times in properties list");
            } elseif (!isset($object->{$property})) {
                self::fail("Property \"{$property}\" does not exist on object, even though it was hinted to be there");
            }

            $propertiesHash[$property] = 1;
        }
    }
}
