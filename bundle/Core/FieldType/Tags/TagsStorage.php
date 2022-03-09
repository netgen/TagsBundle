<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use Ibexa\Contracts\Core\FieldType\GatewayBasedStorage;
use Ibexa\Contracts\Core\FieldType\StorageGateway;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use function count;

/**
 * @property \Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway $gateway
 *
 * @extends \Ibexa\Contracts\Core\FieldType\GatewayBasedStorage<\Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway>
 */
final class TagsStorage extends GatewayBasedStorage
{
    private TagsService $tagsService;

    public function __construct(StorageGateway $gateway, TagsService $tagsService)
    {
        parent::__construct($gateway);

        $this->tagsService = $tagsService;
    }

    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context): ?bool
    {
        $this->gateway->deleteFieldData($versionInfo, [$field->id]);
        if (count($field->value->externalData ?? []) > 0) {
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

        return null;
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context): void
    {
        $this->gateway->getFieldData($versionInfo, $field);
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context): bool
    {
        $this->gateway->deleteFieldData($versionInfo, $fieldIds);

        return true;
    }

    public function hasFieldData(): bool
    {
        return true;
    }

    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context): array
    {
        return [];
    }

    /**
     * Creates a tag from provided data.
     */
    private function createTag(array $tagData): Tag
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
