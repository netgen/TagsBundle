<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\SPI\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\FieldType\StorageGateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\TagsBundle\API\Repository\TagsService;

/**
 * Converter for Tags field type external storage.
 */
class TagsStorage extends GatewayBasedStorage
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\SPI\FieldType\StorageGateway $gateway
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(StorageGateway $gateway, TagsService $tagsService)
    {
        parent::__construct($gateway);

        $this->tagsService = $tagsService;
    }

    /**
     * Stores value for $field in an external data source.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return mixed null|true
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $this->gateway->deleteFieldData($versionInfo, array($field->id));
        if (!empty($field->value->externalData)) {
            $externalData = $field->value->externalData;
            foreach ($externalData as $key => $tag) {
                if (!isset($tag['id'])) {
                    try {
                        $createdTag = $this->createTag($tag);
                        $field->value->externalData[$key]['id'] = $createdTag->id;
                    } catch (UnauthorizedException $e) {
                        // If users cannot create tags, just remove it from
                        // the list of tags to be created
                        unset($field->value->externalData[$key]);
                    }
                }
            }

            $this->gateway->storeFieldData($versionInfo, $field);
        }
    }

    /**
     * Populates $field value property based on the external data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $this->gateway->getFieldData($versionInfo, $field);
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds Array of field IDs
     * @param array $context
     *
     * @return bool
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
        $this->gateway->deleteFieldData($versionInfo, $fieldIds);
    }

    /**
     * Checks if field type has external data to deal with.
     *
     * @return bool
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * Get index data for external data for search backend.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
        return false;
    }

    /**
     * Creates a tag from provided data.
     *
     * @param array $tagData
     *
     * @return \Netgen\TagsBundle\API\Repository\Values\Tags\Tag
     */
    protected function createTag(array $tagData)
    {
        $tagCreateStruct = $this->tagsService->newTagCreateStruct(
            $tagData['parent_id'],
            $tagData['main_language_code']
        );

        foreach ($tagData['keywords'] as $languageCode => $keyword) {
            $tagCreateStruct->setKeyword($keyword, $languageCode);
        }

        if (isset($tagData['remote_id'])) {
            $tagCreateStruct->remoteId = $tagData['remote_id'];
        }

        if (isset($tagData['always_available'])) {
            $tagCreateStruct->alwaysAvailable = $tagData['always_available'];
        }

        return $this->tagsService->createTag($tagCreateStruct);
    }
}
