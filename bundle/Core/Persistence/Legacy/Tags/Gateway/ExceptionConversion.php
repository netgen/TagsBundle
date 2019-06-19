<?php

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

use Doctrine\DBAL\DBALException;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway
     */
    private $innerGateway;

    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function getBasicTagData($tagId)
    {
        try {
            return $this->innerGateway->getBasicTagData($tagId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getBasicTagDataByRemoteId($remoteId)
    {
        try {
            return $this->innerGateway->getBasicTagDataByRemoteId($remoteId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getFullTagData($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getFullTagData($tagId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getFullTagDataByRemoteId($remoteId, array $translations = null, $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getFullTagDataByRemoteId($remoteId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getFullTagDataByKeywordAndParentId($keyword, $parentId, array $translations = null, $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getFullTagDataByKeywordAndParentId($keyword, $parentId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getChildren($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getChildren($tagId, $offset, $limit, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getChildrenCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getChildrenCount($tagId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getTagsByKeyword($keyword, $translation, $useAlwaysAvailable = true, $exactMatch = true, $offset = 0, $limit = -1)
    {
        try {
            return $this->innerGateway->getTagsByKeyword($keyword, $translation, $useAlwaysAvailable, $exactMatch, $offset, $limit);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable = true, $exactMatch = true)
    {
        try {
            return $this->innerGateway->getTagsByKeywordCount($keyword, $translation, $useAlwaysAvailable, $exactMatch);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getSynonyms($tagId, $offset = 0, $limit = -1, array $translations = null, $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getSynonyms($tagId, $offset, $limit, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function getSynonymCount($tagId, array $translations = null, $useAlwaysAvailable = true)
    {
        try {
            return $this->innerGateway->getSynonymCount($tagId, $translations, $useAlwaysAvailable);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function moveSynonym($synonymId, $mainTagData)
    {
        try {
            return $this->innerGateway->moveSynonym($synonymId, $mainTagData);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function create(CreateStruct $createStruct, array $parentTag = null)
    {
        try {
            return $this->innerGateway->create($createStruct, $parentTag);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function update(UpdateStruct $updateStruct, $tagId)
    {
        try {
            $this->innerGateway->update($updateStruct, $tagId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function createSynonym(SynonymCreateStruct $createStruct, array $tag)
    {
        try {
            return $this->innerGateway->createSynonym($createStruct, $tag);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function convertToSynonym($tagId, $mainTagData)
    {
        try {
            return $this->innerGateway->convertToSynonym($tagId, $mainTagData);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function transferTagAttributeLinks($tagId, $targetTagId)
    {
        try {
            $this->innerGateway->transferTagAttributeLinks($tagId, $targetTagId);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function moveSubtree(array $sourceTagData, array $destinationParentTagData = null)
    {
        try {
            $this->innerGateway->moveSubtree($sourceTagData, $destinationParentTagData);
        } catch (DBALException $e) {
            throw new RuntimeException('Database error', 0, $e);
        } catch (PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    public function deleteTag($tagId)
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
