<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Ibexa\Contracts\Core\Persistence\Content\Language\Handler as LanguageHandler;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;

use function array_map;
use function array_pop;
use function array_slice;
use function count;
use function explode;
use function implode;
use function is_array;
use function is_bool;
use function mb_strtolower;
use function mb_substr_count;
use function str_replace;
use function time;
use function trim;

use const PHP_INT_MAX;

final class DoctrineDatabase extends Gateway
{
    public function __construct(
        private Connection $connection,
        private LanguageHandler $languageHandler,
        private LanguageMaskGenerator $languageMaskGenerator,
    ) {}

    public function getBasicTagData(int $tagId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('eztags')
            ->where(
                $query->expr()->eq('id', ':id'),
            )->setParameter('id', $tagId, Types::INTEGER);

        $row = $query->execute()->fetch(FetchMode::ASSOCIATIVE);

        if (is_array($row)) {
            return $row;
        }

        throw new NotFoundException('tag', $tagId);
    }

    public function getBasicTagDataByRemoteId(string $remoteId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('eztags')
            ->where(
                $query->expr()->eq(
                    'remote_id',
                    ':remote_id',
                ),
            )->setParameter('remote_id', $remoteId, Types::STRING);

        $row = $query->execute()->fetch(FetchMode::ASSOCIATIVE);

        if (is_array($row)) {
            return $row;
        }

        throw new NotFoundException('tag', $remoteId);
    }

    public function getFullTagData(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->eq(
                'eztags.id',
                ':id',
            ),
        )->setParameter('id', $tagId, Types::INTEGER);

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getFullTagDataByRemoteId(string $remoteId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->eq(
                'eztags.remote_id',
                ':remote_id',
            ),
        )
        ->setParameter('remote_id', $remoteId, Types::STRING);

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getFullTagDataByKeywordAndParentId(string $keyword, int $parentId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->eq(
                'eztags_keyword.keyword',
                ':keyword',
            ),
            $query->expr()->eq(
                'eztags.parent_id',
                ':parent_id',
            ),
        )->setParameter('keyword', $keyword, Types::STRING)
        ->setParameter('parent_id', $parentId, Types::INTEGER);

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getChildren(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $tagIdsQuery = $this->createTagIdsQuery($translations, $useAlwaysAvailable);
        $tagIdsQuery->andWhere(
            $tagIdsQuery->expr()->andX(
                $tagIdsQuery->expr()->eq(
                    'eztags.parent_id',
                    ':parent_id',
                ),
                $tagIdsQuery->expr()->eq('eztags.main_tag_id', 0),
            ),
        )->setParameter('parent_id', $tagId, Types::INTEGER)
        ->orderBy('eztags.keyword', 'ASC')
        ->setFirstResult($offset)
        ->setMaxResults($limit > 0 ? $limit : PHP_INT_MAX);

        $statement = $tagIdsQuery->execute();

        $tagIds = array_map(
            static fn (array $row): int => (int) $row['id'],
            $statement->fetchAll(FetchMode::ASSOCIATIVE),
        );

        if (count($tagIds) === 0) {
            return [];
        }

        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->in(
                'eztags.id',
                [':id'],
            ),
        )
        ->setParameter('id', $tagIds, Connection::PARAM_INT_ARRAY)
        ->orderBy('eztags_keyword.keyword', 'ASC');

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getChildrenCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        $query = $this->createTagCountQuery($translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->andX(
                $query->expr()->eq(
                    'eztags.parent_id',
                    ':parent_id',
                ),
                $query->expr()->eq('eztags.main_tag_id', 0),
            ),
        )->setParameter('parent_id', $tagId, Types::INTEGER);

        $rows = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        return (int) $rows[0]['count'];
    }

    public function getTagsByKeyword(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true, int $offset = 0, int $limit = -1): array
    {
        $databasePlatform = $this->connection->getDatabasePlatform();
        $tagIdsQuery = $this->createTagIdsQuery([$translation], $useAlwaysAvailable);

        $tagIdsQuery->andWhere(
            $exactMatch
                ? $tagIdsQuery->expr()->eq(
                    'eztags_keyword.keyword',
                    ':keyword',
                )
                : $tagIdsQuery->expr()->like(
                    $databasePlatform->getLowerExpression('eztags_keyword.keyword'),
                    ':keyword',
                ),
        );

        $exactMatch
            ? $tagIdsQuery->setParameter('keyword', $keyword, Types::STRING)
            : $tagIdsQuery->setParameter('keyword', mb_strtolower($keyword) . '%', Types::STRING);

        $tagIdsQuery
            ->setFirstResult($offset)
            ->setMaxResults($limit > 0 ? $limit : PHP_INT_MAX);

        $statement = $tagIdsQuery->execute();

        $tagIds = array_map(
            static fn (array $row): int => (int) $row['id'],
            $statement->fetchAll(FetchMode::ASSOCIATIVE),
        );

        if (count($tagIds) === 0) {
            return [];
        }

        $query = $this->createTagFindQuery([$translation], $useAlwaysAvailable);

        $query->andWhere(
            $query->expr()->in(
                'eztags.id',
                [':id'],
            ),
        )->setParameter('id', $tagIds, Connection::PARAM_INT_ARRAY);

        $query->orderBy('eztags_keyword.keyword', 'ASC');

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getTagsByKeywordCount(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true): int
    {
        $databasePlatform = $this->connection->getDatabasePlatform();
        $query = $this->createTagCountQuery([$translation, $useAlwaysAvailable]);

        $query->andWhere(
            $exactMatch
                ? $query->expr()->eq(
                    'eztags_keyword.keyword',
                    ':keyword',
                )
                : $query->expr()->like(
                    $databasePlatform->getLowerExpression('eztags_keyword.keyword'),
                    ':keyword',
                ),
        );

        $exactMatch
            ? $query->setParameter('keyword', $keyword, Types::STRING)
            : $query->setParameter('keyword', mb_strtolower($keyword) . '%', Types::STRING);

        $rows = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        return (int) $rows[0]['count'];
    }

    public function getSynonyms(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $tagIdsQuery = $this->createTagIdsQuery($translations, $useAlwaysAvailable);
        $tagIdsQuery->andWhere(
            $tagIdsQuery->expr()->eq(
                'eztags.main_tag_id',
                ':main_tag_id',
            ),
        )->setParameter('main_tag_id', $tagId, Types::INTEGER)
        ->setFirstResult($offset)
        ->setMaxResults($limit > 0 ? $limit : PHP_INT_MAX);

        $statement = $tagIdsQuery->execute();

        $tagIds = array_map(
            static fn (array $row): int => (int) $row['id'],
            $statement->fetchAll(FetchMode::ASSOCIATIVE),
        );

        if (count($tagIds) === 0) {
            return [];
        }

        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->in(
                'eztags.id',
                [':id'],
            ),
        )->setParameter('id', $tagIds, Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function getSynonymCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        $query = $this->createTagCountQuery($translations, $useAlwaysAvailable);
        $query->andWhere(
            $query->expr()->eq(
                'eztags.main_tag_id',
                ':main_tag_id',
            ),
        )->setParameter('main_tag_id', $tagId, Types::INTEGER);

        $rows = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        return (int) $rows[0]['count'];
    }

    public function moveSynonym(int $synonymId, array $mainTagData): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set(
                'parent_id',
                ':parent_id',
            )->set(
                'main_tag_id',
                ':main_tag_id',
            )->set(
                'depth',
                ':depth',
            )->set(
                'path_string',
                ':path_string',
            )->where(
                $query->expr()->eq(
                    'id',
                    ':id',
                ),
            )->setParameter('parent_id', $mainTagData['parent_id'], Types::INTEGER)
            ->setParameter('main_tag_id', $mainTagData['id'], Types::INTEGER)
            ->setParameter('depth', $mainTagData['depth'], Types::INTEGER)
            ->setParameter('path_string', $this->getSynonymPathString($synonymId, $mainTagData['path_string']), Types::STRING)
            ->setParameter('id', $synonymId, Types::INTEGER);

        $query->execute();
    }

    public function create(CreateStruct $createStruct, ?array $parentTag = null): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert('eztags')
            ->setValue(
                'parent_id',
                ':parent_id',
            )->setValue(
                'main_tag_id',
                ':main_tag_id',
            )->setValue(
                'modified',
                ':modified',
            )->setValue(
                'keyword',
                ':keyword',
            )->setValue(
                'depth',
                ':depth',
            )->setValue(
                'path_string',
                ':path_string',
            )->setValue(
                'remote_id',
                ':remote_id',
            )->setValue(
                'main_language_id',
                ':main_language_id',
            )->setValue(
                'language_mask',
                ':language_mask',
            )->setParameter('parent_id', $parentTag !== null ? (int) $parentTag['id'] : 0, Types::INTEGER)
            ->setParameter('main_tag_id', 0, Types::INTEGER)
            ->setParameter('modified', time(), Types::INTEGER)
            ->setParameter('keyword', $createStruct->keywords[$createStruct->mainLanguageCode], Types::STRING)
            ->setParameter('depth', $parentTag !== null ? (int) $parentTag['depth'] + 1 : 1, Types::INTEGER)
            ->setParameter('path_string', 'dummy', Types::STRING) // Set later
            ->setParameter('remote_id', $createStruct->remoteId, Types::STRING)
            ->setParameter(
                ':main_language_id',
                $this->languageHandler->loadByLanguageCode(
                    $createStruct->mainLanguageCode,
                )->id,
                Types::INTEGER,
            )->setParameter(
                ':language_mask',
                $this->generateLanguageMask(
                    $createStruct->keywords,
                    is_bool($createStruct->alwaysAvailable) ? $createStruct->alwaysAvailable : true,
                ),
                Types::INTEGER,
            );

        $query->execute();

        $tagId = (int) $this->connection->lastInsertId();

        $pathString = ($parentTag !== null ? $parentTag['path_string'] : '/') . $tagId . '/';

        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set(
                'path_string',
                ':path_string',
            )->where(
                $query->expr()->eq(
                    'id',
                    ':id',
                ),
            )->setParameter('path_string', $pathString, Types::STRING)
            ->setParameter('id', $tagId, Types::INTEGER);

        $query->execute();

        $this->insertTagKeywords(
            $tagId,
            $createStruct->keywords,
            $createStruct->mainLanguageCode,
            $createStruct->alwaysAvailable ?? true,
        );

        return $tagId;
    }

    public function update(UpdateStruct $updateStruct, int $tagId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set(
                'modified',
                ':modified',
            )->set(
                'keyword',
                ':keyword',
            )->set(
                'remote_id',
                ':remote_id',
            )->set(
                'main_language_id',
                ':main_language_id',
            )->set(
                'language_mask',
                ':language_mask',
            )->where(
                $query->expr()->eq(
                    'id',
                    ':id',
                ),
            )->setParameter('id', $tagId, Types::INTEGER)
            ->setParameter('modified', time(), Types::INTEGER)
            ->setParameter('keyword', $updateStruct->keywords[$updateStruct->mainLanguageCode] ?? '', Types::STRING)
            ->setParameter('remote_id', $updateStruct->remoteId, Types::STRING)
            ->setParameter(
                'main_language_id',
                $this->languageHandler->loadByLanguageCode(
                    $updateStruct->mainLanguageCode ?? '',
                )->id,
                Types::INTEGER,
            )
            ->setParameter(
                'language_mask',
                $this->generateLanguageMask(
                    $updateStruct->keywords ?? [],
                    is_bool($updateStruct->alwaysAvailable) ? $updateStruct->alwaysAvailable : true,
                ),
                Types::INTEGER,
            );

        $query->execute();

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('eztags_keyword')
            ->where(
                $query->expr()->eq(
                    'keyword_id',
                    ':keyword_id',
                ),
            )->setParameter('keyword_id', $tagId, Types::INTEGER);

        $query->execute();

        $this->insertTagKeywords(
            $tagId,
            $updateStruct->keywords ?? [],
            $updateStruct->mainLanguageCode ?? '',
            $updateStruct->alwaysAvailable ?? true,
        );
    }

    public function createSynonym(SynonymCreateStruct $createStruct, array $tag): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert('eztags')
            ->setValue(
                'parent_id',
                ':parent_id',
            )->setValue(
                'main_tag_id',
                ':main_tag_id',
            )->setValue(
                'modified',
                ':modified',
            )->setValue(
                'keyword',
                ':keyword',
            )->setValue(
                'depth',
                ':depth',
            )->setValue(
                'path_string',
                ':path_string',
            )->setValue(
                'remote_id',
                ':remote_id',
            )->setValue(
                'main_language_id',
                ':main_language_id',
            )->setValue(
                'language_mask',
                ':language_mask',
            )->setParameter('parent_id', $tag['parent_id'], Types::INTEGER)
            ->setParameter('main_tag_id', $createStruct->mainTagId, Types::INTEGER)
            ->setParameter('modified', time(), Types::INTEGER)
            ->setParameter('keyword', $createStruct->keywords[$createStruct->mainLanguageCode], Types::STRING)
            ->setParameter('depth', $tag['depth'], Types::INTEGER)
            ->setParameter('path_string', 'dummy', Types::STRING) // Set later
            ->setParameter('remote_id', $createStruct->remoteId, Types::STRING)
            ->setParameter(
                'main_language_id',
                $this->languageHandler->loadByLanguageCode(
                    $createStruct->mainLanguageCode,
                )->id,
                Types::INTEGER,
            )
            ->setParameter(
                'language_mask',
                $this->generateLanguageMask(
                    $createStruct->keywords,
                    is_bool($createStruct->alwaysAvailable) ? $createStruct->alwaysAvailable : true,
                ),
                Types::INTEGER,
            );

        $query->execute();

        $synonymId = (int) $this->connection->lastInsertId();

        $synonymPathString = $this->getSynonymPathString($synonymId, $tag['path_string']);

        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set(
                'path_string',
                ':path_string',
            )->where(
                $query->expr()->eq(
                    'id',
                    ':id',
                ),
            )->setParameter('path_string', $synonymPathString, Types::STRING)
            ->setParameter('id', $synonymId, Types::INTEGER);

        $query->execute();

        $this->insertTagKeywords(
            $synonymId,
            $createStruct->keywords,
            $createStruct->mainLanguageCode,
            $createStruct->alwaysAvailable ?? true,
        );

        return $synonymId;
    }

    public function convertToSynonym(int $tagId, array $mainTagData): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set(
                'parent_id',
                ':parent_id',
            )->set(
                'main_tag_id',
                ':main_tag_id',
            )->set(
                'modified',
                ':modified',
            )->set(
                'depth',
                ':depth',
            )->set(
                'path_string',
                ':path_string',
            )->where(
                $query->expr()->eq(
                    'id',
                    ':id',
                ),
            )->setParameter('id', $tagId, Types::INTEGER)
            ->setParameter('parent_id', $mainTagData['parent_id'], Types::INTEGER)
            ->setParameter('main_tag_id', $mainTagData['id'], Types::INTEGER)
            ->setParameter('modified', time(), Types::INTEGER)
            ->setParameter('depth', $mainTagData['depth'], Types::INTEGER)
            ->setParameter('path_string', $this->getSynonymPathString($tagId, $mainTagData['path_string']), Types::STRING);

        $query->execute();
    }

    public function transferTagAttributeLinks(int $tagId, int $targetTagId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('*')
            ->from('eztags_attribute_link')
            ->where(
                $query->expr()->eq(
                    'keyword_id',
                    ':keyword_id',
                ),
            )->setParameter('keyword_id', $tagId, Types::INTEGER);

        $rows = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        $updateLinkIds = [];
        $deleteLinkIds = [];

        foreach ($rows as $row) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->select(
                    'id',
                )
                ->from('eztags_attribute_link')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq(
                            'objectattribute_id',
                            ':objectattribute_id',
                        ),
                        $query->expr()->eq(
                            'objectattribute_version',
                            ':objectattribute_version',
                        ),
                        $query->expr()->eq(
                            'keyword_id',
                            ':keyword_id',
                        ),
                    ),
                )->setParameter('objectattribute_id', $row['objectattribute_id'], Types::INTEGER)
                ->setParameter('objectattribute_version', $row['objectattribute_version'], Types::INTEGER)
                ->setParameter('keyword_id', $targetTagId, Types::INTEGER);

            $targetRows = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);

            if (count($targetRows) === 0) {
                $updateLinkIds[] = $row['id'];
            } else {
                $deleteLinkIds[] = $row['id'];
            }
        }

        if (count($deleteLinkIds) > 0) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->delete('eztags_attribute_link')
                ->where(
                    $query->expr()->in(
                        'id',
                        [':id'],
                    ),
                )->setParameter('id', $deleteLinkIds, Connection::PARAM_INT_ARRAY);

            $query->execute();
        }

        if (count($updateLinkIds) > 0) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->update('eztags_attribute_link')
                ->set(
                    'keyword_id',
                    ':keyword_id',
                )->where(
                    $query->expr()->in(
                        'id',
                        [':id'],
                    ),
                )->setParameter('keyword_id', $targetTagId, Types::INTEGER)
                ->setParameter('id', $updateLinkIds, Connection::PARAM_INT_ARRAY);

            $query->execute();
        }
    }

    public function moveSubtree(array $sourceTagData, ?array $destinationParentTagData = null): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                'id',
                'parent_id',
                'main_tag_id',
                'path_string',
            )
            ->from('eztags')
            ->where(
                $query->expr()->orX(
                    $query->expr()->like(
                        'path_string',
                        ':path_string',
                    ),
                    $query->expr()->eq(
                        'main_tag_id',
                        ':main_tag_id',
                    ),
                ),
            )->setParameter('path_string', $sourceTagData['path_string'] . '%', Types::STRING)
            ->setParameter('main_tag_id', $sourceTagData['id'], Types::INTEGER);

        $rows = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        $oldParentPathString = implode('/', array_slice(explode('/', $sourceTagData['path_string']), 0, -2)) . '/';
        foreach ($rows as $row) {
            // Prefixing ensures correct replacement when there is no parent
            $newPathString = str_replace(
                'prefix' . $oldParentPathString,
                is_array($destinationParentTagData)
                    ? $destinationParentTagData['path_string']
                    : '/',
                'prefix' . $row['path_string'],
            );

            $newParentId = $row['parent_id'];
            if ($row['path_string'] === $sourceTagData['path_string'] || (int) $row['main_tag_id'] === (int) $sourceTagData['id']) {
                $newParentId = (int) implode('', array_slice(explode('/', $newPathString), -3, 1));
            }

            $newDepth = mb_substr_count($newPathString, '/') - 1;

            $query = $this->connection->createQueryBuilder();
            $query
                ->update('eztags')
                ->set(
                    'path_string',
                    ':path_string',
                )->set(
                    'depth',
                    ':depth',
                )->set(
                    'modified',
                    ':modified',
                )->set(
                    'parent_id',
                    ':parent_id',
                )->where(
                    $query->expr()->eq(
                        'id',
                        ':id',
                    ),
                )->setParameter('path_string', $newPathString, Types::STRING)
                ->setParameter('depth', $newDepth, Types::INTEGER)
                ->setParameter('modified', time(), Types::INTEGER)
                ->setParameter('parent_id', $newParentId, Types::INTEGER)
                ->setParameter('id', $row['id'], Types::INTEGER);

            $query->execute();
        }
    }

    public function deleteTag(int $tagId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id')
            ->from('eztags')
            ->where(
                $query->expr()->orX(
                    $query->expr()->like(
                        'path_string',
                        ':path_string',
                    ),
                    $query->expr()->eq(
                        'main_tag_id',
                        ':tag_id',
                    ),
                ),
            )->setParameter('tag_id', $tagId, Types::INTEGER)
            ->setParameter('path_string', '%/' . $tagId . '/%', Types::STRING);

        $statement = $query->execute();

        $tagIds = [];
        while ($row = $statement->fetch(FetchMode::ASSOCIATIVE)) {
            $tagIds[] = (int) $row['id'];
        }

        if (count($tagIds) === 0) {
            return;
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('eztags_attribute_link')
            ->where(
                $query->expr()->in(
                    'keyword_id',
                    [':keyword_id'],
                ),
            )->setParameter('keyword_id', $tagIds, Connection::PARAM_INT_ARRAY);

        $query->execute();

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('eztags_keyword')
            ->where(
                $query->expr()->in(
                    'keyword_id',
                    [':keyword_id'],
                ),
            )->setParameter('keyword_id', $tagIds, Connection::PARAM_INT_ARRAY);

        $query->execute();

        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('eztags')
            ->where(
                $query->expr()->in(
                    'id',
                    [':id'],
                ),
            )->setParameter('id', $tagIds, Connection::PARAM_INT_ARRAY);

        $query->execute();
    }

    public function hideTag(int $tagId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set('is_hidden', '1')
            ->where(
                $query->expr()->eq('id', ':tag_id'),
            )->setParameter('tag_id', $tagId, Types::INTEGER);

        $query->execute();

        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set('is_invisible', '1')
            ->where(
                $query->expr()->like('path_string', ':path_string'),
            )
            ->setParameter('path_string', '%/' . $tagId . '/%', Types::STRING);

        $query->execute();
    }

    public function revealTag(int $tagId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set('is_hidden', '0')
            ->where(
                $query->expr()->eq('id', ':tag_id'),
            )->setParameter('tag_id', $tagId, Types::INTEGER);

        $query->execute();

        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id')
            ->from('eztags')
            ->where($query->expr()->eq('is_hidden', '1'))
            ->andWhere(
                $query->expr()->like('path_string', ':path_string'),
            )
            ->setParameter('path_string', '%/' . $tagId . '/%', Types::STRING);

        $hiddenDescendantTags = array_map(
            static fn (array $row) => $row['id'],
            $query->execute()->fetchAll(FetchMode::ASSOCIATIVE),
        );

        $tagsToRemainInvisible = [];
        foreach ($hiddenDescendantTags as $hiddenDescendantTag) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->select('id')
                ->from('eztags')
                ->where(
                    $query->expr()->like('path_string', ':path_string'),
                )
                ->setParameter('path_string', '%/' . $hiddenDescendantTag . '/%', Types::STRING);

            foreach ($query->execute()->fetchAll(FetchMode::ASSOCIATIVE) as $row) {
                $tagsToRemainInvisible[] = $row['id'];
            }
        }

        $query = $this->connection->createQueryBuilder();
        $query
            ->update('eztags')
            ->set('is_invisible', '0')
            ->where(
                $query->expr()->and(
                    $query->expr()->like('path_string', ':path_string'),
                    $query->expr()->notIn('id', $tagsToRemainInvisible),
                ),
            )
            ->setParameter('path_string', '%/' . $tagId . '/%', Types::STRING);

        $query->execute();
    }

    private function createTagIdsQuery(?array $translations = null, bool $useAlwaysAvailable = true): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT eztags.id, eztags.keyword')
        ->from('eztags', 'eztags')
        // @todo: Joining with eztags_keyword is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        ->leftJoin(
            'eztags',
            'eztags_keyword',
            'eztags_keyword',
            $query->expr()->andX(
                // eztags_keyword.locale is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr()->eq(
                    'eztags_keyword.keyword_id',
                    'eztags.id',
                ),
                $query->expr()->eq(
                    'eztags_keyword.status',
                    ':status',
                ),
            ),
        )->setParameter('status', 1, Types::INTEGER);

        if (count($translations ?? []) > 0) {
            if ($useAlwaysAvailable) {
                $query->andWhere(
                    $query->expr()->orX(
                        $query->expr()->in(
                            'eztags_keyword.locale',
                            [':locale'],
                        ),
                        $query->expr()->andX(
                            $query->expr()->gt(
                                $this->connection->getDatabasePlatform()->getBitAndComparisonExpression(
                                    'eztags.language_mask',
                                    1,
                                ),
                                0,
                            ),
                            $query->expr()->eq(
                                'eztags.main_language_id',
                                $this->connection->getDatabasePlatform()->getBitAndComparisonExpression(
                                    'eztags_keyword.language_id',
                                    -2, // -2 == PHP_INT_MAX << 1
                                ),
                            ),
                        ),
                    ),
                );
            } else {
                $query->andWhere(
                    $query->expr()->in(
                        'eztags_keyword.locale',
                        [':locale'],
                    ),
                );
            }

            $query->setParameter('locale', $translations, Connection::PARAM_STR_ARRAY);
        }

        return $query;
    }

    /**
     * Creates a select query for tag objects.
     *
     * Creates a select query with all necessary joins to fetch a complete
     * tag. Does not apply any WHERE conditions.
     */
    private function createTagFindQuery(?array $translations = null, bool $useAlwaysAvailable = true): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            // Tag
            'eztags.id',
            'eztags.parent_id',
            'eztags.main_tag_id',
            'eztags.depth',
            'eztags.path_string',
            'eztags.modified',
            'eztags.remote_id',
            'eztags.main_language_id',
            'eztags.language_mask',
            'eztags.is_hidden',
            'eztags.is_invisible',
            // Tag keywords
            'eztags_keyword.keyword',
            'eztags_keyword.locale',
        )->from('eztags')
        // @todo: Joining with eztags_keyword is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        ->leftJoin(
            'eztags',
            'eztags_keyword',
            'eztags_keyword',
            $query->expr()->andX(
                // eztags_keyword.locale is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr()->eq(
                    'eztags_keyword.keyword_id',
                    'eztags.id',
                ),
                $query->expr()->eq(
                    'eztags_keyword.status',
                    ':status',
                ),
            ),
        )->setParameter('status', 1, Types::INTEGER);

        if (count($translations ?? []) > 0) {
            if ($useAlwaysAvailable) {
                $query->andWhere(
                    $query->expr()->orX(
                        $query->expr()->in(
                            'eztags_keyword.locale',
                            [':locale'],
                        ),
                        $query->expr()->andX(
                            $query->expr()->gt(
                                $this->connection->getDatabasePlatform()->getBitAndComparisonExpression(
                                    'eztags.language_mask',
                                    1,
                                ),
                                0,
                            ),
                            $query->expr()->eq(
                                'eztags.main_language_id',
                                $this->connection->getDatabasePlatform()->getBitAndComparisonExpression(
                                    'eztags_keyword.language_id',
                                    -2, // -2 == PHP_INT_MAX << 1
                                ),
                            ),
                        ),
                    ),
                );
            } else {
                $query->andWhere(
                    $query->expr()->in(
                        'eztags_keyword.locale',
                        [':locale'],
                    ),
                );
            }

            $query->setParameter('locale', $translations, Connection::PARAM_STR_ARRAY);
        }

        return $query;
    }

    /**
     * Creates a select count query for tag objects.
     *
     * Creates a select query with all necessary joins to fetch a complete
     * tag. Does not apply any WHERE conditions.
     */
    private function createTagCountQuery(?array $translations = null, bool $useAlwaysAvailable = true): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            $this->connection->getDatabasePlatform()->getCountExpression('DISTINCT eztags.id') . ' AS count',
        )->from('eztags')
        // @todo: Joining with eztags_keyword is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        ->leftJoin(
            'eztags',
            'eztags_keyword',
            'eztags_keyword',
            $query->expr()->andX(
                // eztags_keyword.locale is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr()->eq(
                    'eztags_keyword.keyword_id',
                    'eztags.id',
                ),
                $query->expr()->eq(
                    'eztags_keyword.status',
                    ':status',
                ),
            ),
        )->setParameter('status', 1, Types::INTEGER);

        if (count($translations ?? []) > 0) {
            if ($useAlwaysAvailable) {
                $query->andWhere(
                    $query->expr()->orX(
                        $query->expr()->in(
                            'eztags_keyword.locale',
                            [':locale'],
                        ),
                        $query->expr()->andX(
                            $query->expr()->gt(
                                $this->connection->getDatabasePlatform()->getBitAndComparisonExpression(
                                    'eztags.language_mask',
                                    1,
                                ),
                                0,
                            ),
                            $query->expr()->eq(
                                'eztags.main_language_id',
                                $this->connection->getDatabasePlatform()->getBitAndComparisonExpression(
                                    'eztags_keyword.language_id',
                                    -2, // -2 == PHP_INT_MAX << 1
                                ),
                            ),
                        ),
                    ),
                );
            } else {
                $query->andWhere(
                    $query->expr()->in(
                        'eztags_keyword.locale',
                        [':locale'],
                    ),
                );
            }

            $query->setParameter('locale', $translations, Connection::PARAM_STR_ARRAY);
        }

        return $query;
    }

    /**
     * Inserts keywords for tag with provided tag ID.
     */
    private function insertTagKeywords(int $tagId, array $keywords, string $mainLanguageCode, bool $alwaysAvailable): void
    {
        foreach ($keywords as $languageCode => $keyword) {
            $query = $this->connection->createQueryBuilder();
            $query
                ->insert('eztags_keyword')
                ->setValue(
                    'keyword_id',
                    ':keyword_id',
                )->setValue(
                    'language_id',
                    ':language_id',
                )->setValue(
                    'keyword',
                    ':keyword',
                )->setValue(
                    'locale',
                    ':locale',
                )->setValue(
                    'status',
                    ':status',
                )->setParameter('keyword_id', $tagId, Types::INTEGER)
                ->setParameter(
                    'language_id',
                    $this->languageHandler->loadByLanguageCode(
                        $languageCode,
                    )->id + (int) ($languageCode === $mainLanguageCode && $alwaysAvailable),
                    Types::INTEGER,
                )
                ->setParameter('keyword', $keyword, Types::STRING)
                ->setParameter('locale', $languageCode, Types::STRING)
                ->setParameter('status', 1, Types::INTEGER);

            $query->execute();
        }
    }

    /**
     * Returns the path string of a synonym for main tag path string.
     */
    private function getSynonymPathString(int $synonymId, string $mainTagPathString): string
    {
        $pathStringElements = explode('/', trim($mainTagPathString, '/'));
        array_pop($pathStringElements);

        return (count($pathStringElements) > 0 ? '/' . implode('/', $pathStringElements) : '') . '/' . $synonymId . '/';
    }

    /**
     * Generates a language mask for provided keywords.
     */
    private function generateLanguageMask(array $keywords, bool $alwaysAvailable = true): int
    {
        $languages = [];

        foreach ($keywords as $languageCode => $keyword) {
            $languages[$languageCode] = true;
        }

        if ($alwaysAvailable) {
            $languages['always-available'] = true;
        }

        return $this->languageMaskGenerator->generateLanguageMask($languages);
    }
}
