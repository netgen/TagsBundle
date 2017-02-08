<?php

namespace Netgen\TagsBundle\Tests\Core\Repository\Values\Tags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    /**
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::getProperties
     */
    public function testObjectProperties()
    {
        $object = new Tag();
        $properties = $object->attributes();
        $this->assertContains('id', $properties, 'Property not found');
        $this->assertContains('parentTagId', $properties, 'Property not found');
        $this->assertContains('mainTagId', $properties, 'Property not found');
        $this->assertContains('keywords', $properties, 'Property not found');
        $this->assertContains('depth', $properties, 'Property not found');
        $this->assertContains('pathString', $properties, 'Property not found');
        $this->assertContains('modificationDate', $properties, 'Property not found');
        $this->assertContains('remoteId', $properties, 'Property not found');
        $this->assertContains('mainLanguageCode', $properties, 'Property not found');
        $this->assertContains('alwaysAvailable', $properties, 'Property not found');
        $this->assertContains('languageCodes', $properties, 'Property not found');

        // check for duplicates and double check existence of property
        $propertiesHash = array();
        foreach ($properties as $property) {
            if (isset($propertiesHash[$property])) {
                $this->fail("Property \"{$property}\" exists several times in properties list");
            } elseif (!isset($object->$property)) {
                $this->fail("Property \"{$property}\" does not exist on object, even though it was hinted to be there");
            }

            $propertiesHash[$property] = 1;
        }
    }
}
