<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

use Doctrine\DBAL\DBALException;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use PDOException;
use RuntimeException;

final class ExceptionConversion extends Gateway
{
    public function __construct(private Gateway $innerGateway) {}

    public function getBasicTagData(int $tagId): array
    {
        try {
            return $this->innerGateway->getBasicTagData($tagId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getBasicTagDataByRemoteId(string $remoteId): array
    {
        try {
            return $this->innerGateway->getBasicTagDataByRemoteId($remoteId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getFullTagData(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        try {
            return $this->innerGateway->getFullTagData($tagId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getFullTagDataByRemoteId(string $remoteId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        try {
            return $this->innerGateway->getFullTagDataByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getFullTagDataByKeywordAndParentId(string $keyword, int $parentId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        try {
            return $this->innerGateway->getFullTagDataByKeywordAndParentId($keyword, $parentId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getChildren(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        try {
            return $this->innerGateway->getChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getChildrenCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        try {
            return $this->innerGateway->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getTagsByKeyword(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true, int $offset = 0, int $limit = -1): array
    {
        try {
            return $this->innerGateway->getTagsByKeyword($keyword, $translation, $useAlwaysAvailable, $exactMatch, $offset, $limit);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getTagsByKeywordCount(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true): int
    {
        try {
            return $this->innerGateway->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable, $exactMatch);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getSynonyms(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        try {
            return $this->innerGateway->getSynonyms($tagId, $offset, $limit, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getSynonymCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        try {
            return $this->innerGateway->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function moveSynonym(int $synonymId, array $mainTagData): void
    {
        try {
            $this->innerGateway->moveSynonym($synonymId, $mainTagData);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function create(CreateStruct $createStruct, ?array $parentTag = null): int
    {
        try {
            return $this->innerGateway->create($createStruct, $parentTag);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function update(UpdateStruct $updateStruct, int $tagId): void
    {
        try {
            $this->innerGateway->update($updateStruct, $tagId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function createSynonym(SynonymCreateStruct $createStruct, array $tag): int
    {
        try {
            return $this->innerGateway->createSynonym($createStruct, $tag);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function convertToSynonym(int $tagId, array $mainTagData): void
    {
        try {
            $this->innerGateway->convertToSynonym($tagId, $mainTagData);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function transferTagAttributeLinks(int $tagId, int $targetTagId): void
    {
        try {
            $this->innerGateway->transferTagAttributeLinks($tagId, $targetTagId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function moveSubtree(array $sourceTagData, ?array $destinationParentTagData = null): void
    {
        try {
            $this->innerGateway->moveSubtree($sourceTagData, $destinationParentTagData);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function deleteTag(int $tagId): void
    {
        try {
            $this->innerGateway->deleteTag($tagId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
