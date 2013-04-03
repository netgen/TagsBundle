<?php

namespace Netgen\TagsBundle\Tests\Core\Repository\Values\Tags;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use PHPUnit_Framework_TestCase;

class TagTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \Netgen\TagsBundle\API\Repository\Values\Tags\Tag::getProperties
     */
    public function testObjectProperties()
    {
        $object = new Tag();
        $properties = $object->attributes();
        $this->assertContains( "id", $properties, "Property not found" );
        $this->assertContains( "parentTagId", $properties, "Property not found" );
        $this->assertContains( "mainTagId", $properties, "Property not found" );
        $this->assertContains( "keyword", $properties, "Property not found" );
        $this->assertContains( "depth", $properties, "Property not found" );
        $this->assertContains( "pathString", $properties, "Property not found" );
        $this->assertContains( "modificationDate", $properties, "Property not found" );
        $this->assertContains( "remoteId", $properties, "Property not found" );

        // check for duplicates and double check existence of property
        $propertiesHash = array();
        foreach ( $properties as $property )
        {
            if ( isset( $propertiesHash[$property] ) )
            {
                $this->fail( "Property \"{$property}\" exists several times in properties list" );
            }
            else if ( !isset( $object->$property ) )
            {
                $this->fail( "Property \"{$property}\" does not exist on object, even though it was hinted to be there" );
            }

            $propertiesHash[$property] = 1;
        }
    }
}
