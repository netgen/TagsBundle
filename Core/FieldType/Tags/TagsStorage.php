<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Converter for Tags field type external storage
 */
class TagsStorage extends GatewayBasedStorage
{
    /**
     * Stores value for $field in an external data source.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return mixed null|true
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /** @var \Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );

        $gateway->deleteFieldData( $versionInfo, array( $field->id ) );
        if ( !empty( $field->value->externalData ) )
        {
            $gateway->storeFieldData( $versionInfo, $field );
        }
    }

    /**
     * Populates $field value property based on the external data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        /** @var \Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );
        $gateway->getFieldData( $versionInfo, $field );
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds Array of field IDs
     * @param array $context
     *
     * @return boolean
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        /** @var \Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway $gateway */
        $gateway = $this->getGateway( $context );
        $gateway->deleteFieldData( $versionInfo, $fieldIds );
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * Get index data for external data for search backend
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        return false;
    }
}
