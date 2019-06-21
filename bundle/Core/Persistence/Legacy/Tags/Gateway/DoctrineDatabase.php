<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use Netgen\TagsBundle\Core\Persistence\Legacy\Tags\Gateway;
use Netgen\TagsBundle\SPI\Persistence\Tags\CreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\SynonymCreateStruct;
use Netgen\TagsBundle\SPI\Persistence\Tags\UpdateStruct;
use PDO;

final class DoctrineDatabase extends Gateway
{
    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $handler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    private $languageHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    private $languageMaskGenerator;

    public function __construct(
        DatabaseHandler $handler,
        LanguageHandler $languageHandler,
        LanguageMaskGenerator $languageMaskGenerator
    ) {
        $this->handler = $handler;
        $this->languageHandler = $languageHandler;
        $this->languageMaskGenerator = $languageMaskGenerator;
    }

    public function getBasicTagData(int $tagId): array
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->handler->quoteTable('eztags'))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($tagId, null, PDO::PARAM_INT)
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        }

        throw new NotFoundException('tag', $tagId);
    }

    public function getBasicTagDataByRemoteId(string $remoteId): array
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->handler->quoteTable('eztags'))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('remote_id'),
                    $query->bindValue($remoteId, null, PDO::PARAM_STR)
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            return $row;
        }

        throw new NotFoundException('tag', $remoteId);
    }

    public function getFullTagData(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('id', 'eztags'),
                $query->bindValue($tagId, null, PDO::PARAM_INT)
            )
        );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFullTagDataByRemoteId(string $remoteId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('remote_id', 'eztags'),
                $query->bindValue($remoteId, null, PDO::PARAM_STR)
            )
        );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFullTagDataByKeywordAndParentId(string $keyword, int $parentId, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('keyword', 'eztags_keyword'),
                $query->bindValue($keyword, null, PDO::PARAM_STR)
            ),
            $query->expr->eq(
                $this->handler->quoteColumn('parent_id', 'eztags'),
                $query->bindValue($parentId, null, PDO::PARAM_INT)
            )
        );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChildren(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $tagIdsQuery = $this->createTagIdsQuery($translations, $useAlwaysAvailable);
        $tagIdsQuery->where(
            $tagIdsQuery->expr->lAnd(
                $tagIdsQuery->expr->eq(
                    $this->handler->quoteColumn('parent_id', 'eztags'),
                    $tagIdsQuery->bindValue($tagId, null, PDO::PARAM_INT)
                ),
                $tagIdsQuery->expr->eq($this->handler->quoteColumn('main_tag_id', 'eztags'), 0)
            )
        )
        ->orderBy(
            $this->handler->quoteColumn('keyword', 'eztags'),
            $tagIdsQuery::ASC
        )
        ->limit($limit > 0 ? $limit : PHP_INT_MAX, $offset);

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $tagIdsQuery->prepare();
        $statement->execute();

        $tagIds = array_map(
            static function (array $row): int {
                return (int) $row['id'];
            },
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );

        if (count($tagIds) === 0) {
            return [];
        }

        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->where(
            $query->expr->in(
                $this->handler->quoteColumn('id', 'eztags'),
                $tagIds
            )
        )
        ->orderBy(
            $this->handler->quoteColumn('keyword', 'eztags_keyword'),
            $query::ASC
        );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChildrenCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        $query = $this->createTagCountQuery($translations, $useAlwaysAvailable);
        $query->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->handler->quoteColumn('parent_id', 'eztags'),
                    $query->bindValue($tagId, null, PDO::PARAM_INT)
                ),
                $query->expr->eq($this->handler->quoteColumn('main_tag_id', 'eztags'), 0)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return (int) $rows[0]['count'];
    }

    public function getTagsByKeyword(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true, int $offset = 0, int $limit = -1): array
    {
        $tagIdsQuery = $this->createTagIdsQuery([$translation], $useAlwaysAvailable);
        $tagIdsQuery->where(
            $exactMatch ?
                $tagIdsQuery->expr->eq(
                    $this->handler->quoteColumn('keyword', 'eztags_keyword'),
                    $tagIdsQuery->bindValue($keyword, null, PDO::PARAM_STR)
                ) :
                $tagIdsQuery->expr->like(
                    $this->handler->quoteColumn('keyword', 'eztags_keyword'),
                    $tagIdsQuery->bindValue($keyword . '%', null, PDO::PARAM_STR)
                )
        );

        $tagIdsQuery->limit($limit > 0 ? $limit : PHP_INT_MAX, $offset);

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $tagIdsQuery->prepare();
        $statement->execute();

        $tagIds = array_map(
            static function (array $row): int {
                return (int) $row['id'];
            },
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );

        if (count($tagIds) === 0) {
            return [];
        }

        $query = $this->createTagFindQuery([$translation], $useAlwaysAvailable);

        $query->where(
            $query->expr->in(
                $this->handler->quoteColumn('id', 'eztags'),
                $tagIds
            )
        );

        $query->orderBy(
            $this->handler->quoteColumn('keyword', 'eztags_keyword'),
            $query::ASC
        );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTagsByKeywordCount(string $keyword, string $translation, bool $useAlwaysAvailable = true, bool $exactMatch = true): int
    {
        $query = $this->createTagCountQuery([$translation, $useAlwaysAvailable]);

        $query->where(
            $exactMatch ?
                $query->expr->eq(
                    $this->handler->quoteColumn('keyword', 'eztags_keyword'),
                    $query->bindValue($keyword, null, PDO::PARAM_STR)
                ) :
                $query->expr->like(
                    $this->handler->quoteColumn('keyword', 'eztags_keyword'),
                    $query->bindValue($keyword . '%', null, PDO::PARAM_STR)
                )
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return (int) $rows[0]['count'];
    }

    public function getSynonyms(int $tagId, int $offset = 0, int $limit = -1, ?array $translations = null, bool $useAlwaysAvailable = true): array
    {
        $tagIdsQuery = $this->createTagIdsQuery($translations, $useAlwaysAvailable);
        $tagIdsQuery->where(
            $tagIdsQuery->expr->eq(
                $this->handler->quoteColumn('main_tag_id', 'eztags'),
                $tagIdsQuery->bindValue($tagId, null, PDO::PARAM_INT)
            )
        )
        ->limit($limit > 0 ? $limit : PHP_INT_MAX, $offset);

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $tagIdsQuery->prepare();
        $statement->execute();

        $tagIds = array_map(
            static function (array $row): int {
                return (int) $row['id'];
            },
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );

        if (count($tagIds) === 0) {
            return [];
        }

        $query = $this->createTagFindQuery($translations, $useAlwaysAvailable);
        $query->where(
            $query->expr->in(
                $this->handler->quoteColumn('id', 'eztags'),
                $tagIds
            )
        );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSynonymCount(int $tagId, ?array $translations = null, bool $useAlwaysAvailable = true): int
    {
        $query = $this->createTagCountQuery($translations, $useAlwaysAvailable);
        $query->where(
            $query->expr->eq(
                $this->handler->quoteColumn('main_tag_id', 'eztags'),
                $query->bindValue($tagId, null, PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return (int) $rows[0]['count'];
    }

    public function moveSynonym(int $synonymId, array $mainTagData): void
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('eztags'))
            ->set(
                $this->handler->quoteColumn('parent_id'),
                $query->bindValue($mainTagData['parent_id'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('main_tag_id'),
                $query->bindValue($mainTagData['id'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('depth'),
                $query->bindValue($mainTagData['depth'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue($this->getSynonymPathString($synonymId, $mainTagData['path_string']), null, PDO::PARAM_STR)
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($synonymId, null, PDO::PARAM_INT)
                )
            );

        $query->prepare()->execute();
    }

    public function create(CreateStruct $createStruct, ?array $parentTag = null): int
    {
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto($this->handler->quoteTable('eztags'))
            ->set(
                $this->handler->quoteColumn('id'),
                $this->handler->getAutoIncrementValue('eztags', 'id')
            )->set(
                $this->handler->quoteColumn('parent_id'),
                $query->bindValue($parentTag !== null ? (int) $parentTag['id'] : 0, null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('main_tag_id'),
                $query->bindValue(0, null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('modified'),
                $query->bindValue(time(), null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('keyword'),
                $query->bindValue($createStruct->keywords[$createStruct->mainLanguageCode], null, PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('depth'),
                $query->bindValue($parentTag !== null ? (int) $parentTag['depth'] + 1 : 1, null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue('dummy') // Set later
            )->set(
                $this->handler->quoteColumn('remote_id'),
                $query->bindValue($createStruct->remoteId, null, PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('main_language_id'),
                $query->bindValue(
                    $this->languageHandler->loadByLanguageCode(
                        $createStruct->mainLanguageCode
                    )->id,
                    null,
                    PDO::PARAM_INT
                )
            )->set(
                $this->handler->quoteColumn('language_mask'),
                $query->bindValue(
                    $this->generateLanguageMask(
                        $createStruct->keywords,
                        is_bool($createStruct->alwaysAvailable) ? $createStruct->alwaysAvailable : true
                    ),
                    null,
                    PDO::PARAM_INT
                )
            );

        $query->prepare()->execute();

        $tagId = (int) $this->handler->lastInsertId($this->handler->getSequenceName('eztags', 'id'));
        $pathString = ($parentTag !== null ? $parentTag['path_string'] : '/') . $tagId . '/';

        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('eztags'))
            ->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue($pathString, null, PDO::PARAM_STR)
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($tagId, null, PDO::PARAM_INT)
                )
            );

        $query->prepare()->execute();

        $this->insertTagKeywords(
            $tagId,
            $createStruct->keywords,
            $createStruct->mainLanguageCode,
            $createStruct->alwaysAvailable ?? true
        );

        return $tagId;
    }

    public function update(UpdateStruct $updateStruct, int $tagId): void
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('eztags'))
            ->set(
                $this->handler->quoteColumn('modified'),
                $query->bindValue(time(), null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('keyword'),
                $query->bindValue($updateStruct->keywords[$updateStruct->mainLanguageCode], null, PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('remote_id'),
                $query->bindValue($updateStruct->remoteId, null, PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('main_language_id'),
                $query->bindValue(
                    $this->languageHandler->loadByLanguageCode(
                        $updateStruct->mainLanguageCode ?? ''
                    )->id,
                    null,
                    PDO::PARAM_INT
                )
            )->set(
                $this->handler->quoteColumn('language_mask'),
                $query->bindValue(
                    $this->generateLanguageMask(
                        $updateStruct->keywords ?? [],
                        is_bool($updateStruct->alwaysAvailable) ? $updateStruct->alwaysAvailable : true
                    ),
                    null,
                    PDO::PARAM_INT
                )
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($tagId, null, PDO::PARAM_INT)
                )
            );

        $query->prepare()->execute();

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom($this->handler->quoteTable('eztags_keyword'))
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn('keyword_id'),
                    $tagId
                )
            );

        $query->prepare()->execute();

        $this->insertTagKeywords(
            $tagId,
            $updateStruct->keywords ?? [],
            $updateStruct->mainLanguageCode ?? '',
            $updateStruct->alwaysAvailable ?? true
        );
    }

    public function createSynonym(SynonymCreateStruct $createStruct, array $tag): int
    {
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto($this->handler->quoteTable('eztags'))
            ->set(
                $this->handler->quoteColumn('id'),
                $this->handler->getAutoIncrementValue('eztags', 'id')
            )->set(
                $this->handler->quoteColumn('parent_id'),
                $query->bindValue($tag['parent_id'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('main_tag_id'),
                $query->bindValue($createStruct->mainTagId, null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('modified'),
                $query->bindValue(time(), null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('keyword'),
                $query->bindValue($createStruct->keywords[$createStruct->mainLanguageCode], null, PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('depth'),
                $query->bindValue($tag['depth'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue('dummy') // Set later
            )->set(
                $this->handler->quoteColumn('remote_id'),
                $query->bindValue($createStruct->remoteId, null, PDO::PARAM_STR)
            )->set(
                $this->handler->quoteColumn('main_language_id'),
                $query->bindValue(
                    $this->languageHandler->loadByLanguageCode(
                        $createStruct->mainLanguageCode
                    )->id,
                    null,
                    PDO::PARAM_INT
                )
            )->set(
                $this->handler->quoteColumn('language_mask'),
                $query->bindValue(
                    $this->generateLanguageMask(
                        $createStruct->keywords,
                        is_bool($createStruct->alwaysAvailable) ? $createStruct->alwaysAvailable : true
                    ),
                    null,
                    PDO::PARAM_INT
                )
            );

        $query->prepare()->execute();

        $synonymId = (int) $this->handler->lastInsertId($this->handler->getSequenceName('eztags', 'id'));
        $synonymPathString = $this->getSynonymPathString($synonymId, $tag['path_string']);

        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('eztags'))
            ->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue($synonymPathString, null, PDO::PARAM_STR)
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($synonymId, null, PDO::PARAM_INT)
                )
            );

        $query->prepare()->execute();

        $this->insertTagKeywords(
            $synonymId,
            $createStruct->keywords,
            $createStruct->mainLanguageCode,
            $createStruct->alwaysAvailable ?? true
        );

        return $synonymId;
    }

    public function convertToSynonym(int $tagId, array $mainTagData): void
    {
        $query = $this->handler->createUpdateQuery();
        $query
            ->update($this->handler->quoteTable('eztags'))
            ->set(
                $this->handler->quoteColumn('parent_id'),
                $query->bindValue($mainTagData['parent_id'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('main_tag_id'),
                $query->bindValue($mainTagData['id'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('modified'),
                $query->bindValue(time(), null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('depth'),
                $query->bindValue($mainTagData['depth'], null, PDO::PARAM_INT)
            )->set(
                $this->handler->quoteColumn('path_string'),
                $query->bindValue($this->getSynonymPathString($tagId, $mainTagData['path_string']), null, PDO::PARAM_STR)
            )->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('id'),
                    $query->bindValue($tagId, null, PDO::PARAM_INT)
                )
            );

        $query->prepare()->execute();
    }

    public function transferTagAttributeLinks(int $tagId, int $targetTagId): void
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('*')
            ->from($this->handler->quoteTable('eztags_attribute_link'))
            ->where(
                $query->expr->eq(
                    $this->handler->quoteColumn('keyword_id'),
                    $query->bindValue($tagId, null, PDO::PARAM_INT)
                )
            );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $updateLinkIds = [];
        $deleteLinkIds = [];

        foreach ($rows as $row) {
            $query = $this->handler->createSelectQuery();
            $query
                ->select(
                    $this->handler->quoteColumn('id')
                )
                ->from($this->handler->quoteTable('eztags_attribute_link'))
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq(
                            $this->handler->quoteColumn('objectattribute_id'),
                            $query->bindValue($row['objectattribute_id'], null, PDO::PARAM_INT)
                        ),
                        $query->expr->eq(
                            $this->handler->quoteColumn('objectattribute_version'),
                            $query->bindValue($row['objectattribute_version'], null, PDO::PARAM_INT)
                        ),
                        $query->expr->eq(
                            $this->handler->quoteColumn('keyword_id'),
                            $query->bindValue($targetTagId, null, PDO::PARAM_INT)
                        )
                    )
                );

            /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
            $statement = $query->prepare();
            $statement->execute();

            $targetRows = $statement->fetchAll(PDO::FETCH_ASSOC);

            if (count($targetRows) === 0) {
                $updateLinkIds[] = $row['id'];
            } else {
                $deleteLinkIds[] = $row['id'];
            }
        }

        if (count($deleteLinkIds) > 0) {
            $query = $this->handler->createDeleteQuery();
            $query
                ->deleteFrom($this->handler->quoteTable('eztags_attribute_link'))
                ->where(
                    $query->expr->in(
                        $this->handler->quoteColumn('id'),
                        $deleteLinkIds
                    )
                );

            $query->prepare()->execute();
        }

        if (count($updateLinkIds) > 0) {
            $query = $this->handler->createUpdateQuery();
            $query
                ->update($this->handler->quoteTable('eztags_attribute_link'))
                ->set(
                    $this->handler->quoteColumn('keyword_id'),
                    $query->bindValue($targetTagId)
                )->where(
                    $query->expr->in(
                        $this->handler->quoteColumn('id'),
                        $updateLinkIds
                    )
                );

            $query->prepare()->execute();
        }
    }

    public function moveSubtree(array $sourceTagData, ?array $destinationParentTagData = null): void
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select(
                $this->handler->quoteColumn('id'),
                $this->handler->quoteColumn('parent_id'),
                $this->handler->quoteColumn('main_tag_id'),
                $this->handler->quoteColumn('path_string')
            )
            ->from($this->handler->quoteTable('eztags'))
            ->where(
                $query->expr->lOr(
                    $query->expr->like(
                        $this->handler->quoteColumn('path_string'),
                        $query->bindValue($sourceTagData['path_string'] . '%', null, PDO::PARAM_STR)
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn('main_tag_id'),
                        $query->bindValue($sourceTagData['id'], null, PDO::PARAM_INT)
                    )
                )
            );

        /** @var \Doctrine\DBAL\Driver\PDOStatement $statement */
        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $oldParentPathString = implode('/', array_slice(explode('/', $sourceTagData['path_string']), 0, -2)) . '/';
        foreach ($rows as $row) {
            // Prefixing ensures correct replacement when there is no parent
            $newPathString = str_replace(
                'prefix' . $oldParentPathString,
                $destinationParentTagData ?
                    $destinationParentTagData['path_string'] :
                    '/',
                'prefix' . $row['path_string']
            );

            $newParentId = $row['parent_id'];
            if ($row['path_string'] === $sourceTagData['path_string'] || (int) $row['main_tag_id'] === (int) $sourceTagData['id']) {
                $newParentId = (int) implode('', array_slice(explode('/', $newPathString), -3, 1));
            }

            $newDepth = mb_substr_count($newPathString, '/') - 1;

            $query = $this->handler->createUpdateQuery();
            $query
                ->update($this->handler->quoteTable('eztags'))
                ->set(
                    $this->handler->quoteColumn('path_string'),
                    $query->bindValue($newPathString, null, PDO::PARAM_STR)
                )->set(
                    $this->handler->quoteColumn('depth'),
                    $query->bindValue($newDepth, null, PDO::PARAM_INT)
                )->set(
                    $this->handler->quoteColumn('modified'),
                    $query->bindValue(time(), null, PDO::PARAM_INT)
                )->set(
                    $this->handler->quoteColumn('parent_id'),
                    $query->bindValue($newParentId, null, PDO::PARAM_INT)
                )->where(
                    $query->expr->eq(
                        $this->handler->quoteColumn('id'),
                        $query->bindValue($row['id'], null, PDO::PARAM_INT)
                    )
                );

            $query->prepare()->execute();
        }
    }

    public function deleteTag(int $tagId): void
    {
        $query = $this->handler->createSelectQuery();
        $query
            ->select('id')
            ->from($this->handler->quoteTable('eztags'))
            ->where(
                $query->expr->lOr(
                    $query->expr->like(
                        $this->handler->quoteColumn('path_string'),
                        $query->bindValue('%/' . (int) $tagId . '/%', null, PDO::PARAM_STR)
                    ),
                    $query->expr->eq(
                        $this->handler->quoteColumn('main_tag_id'),
                        $query->bindValue($tagId, null, PDO::PARAM_INT)
                    )
                )
            );

        $statement = $query->prepare();
        $statement->execute();

        $tagIds = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $tagIds[] = (int) $row['id'];
        }

        if (count($tagIds) === 0) {
            return;
        }

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom($this->handler->quoteTable('eztags_attribute_link'))
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn('keyword_id'),
                    $tagIds
                )
            );

        $query->prepare()->execute();

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom($this->handler->quoteTable('eztags_keyword'))
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn('keyword_id'),
                    $tagIds
                )
            );

        $query->prepare()->execute();

        $query = $this->handler->createDeleteQuery();
        $query
            ->deleteFrom($this->handler->quoteTable('eztags'))
            ->where(
                $query->expr->in(
                    $this->handler->quoteColumn('id'),
                    $tagIds
                )
            );

        $query->prepare()->execute();
    }

    private function createTagIdsQuery(?array $translations = null, bool $useAlwaysAvailable = true): SelectQuery
    {
        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $query = $this->handler->createSelectQuery();
        $query->select('DISTINCT eztags.id, eztags.keyword')
        ->from(
            $this->handler->quoteTable('eztags')
        )
        // @todo: Joining with eztags_keyword is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        ->leftJoin(
            $this->handler->quoteTable('eztags_keyword'),
            $query->expr->lAnd(
                // eztags_keyword.locale is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr->eq(
                    $this->handler->quoteColumn('keyword_id', 'eztags_keyword'),
                    $this->handler->quoteColumn('id', 'eztags')
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('status', 'eztags_keyword'),
                    $query->bindValue(1, null, PDO::PARAM_INT)
                )
            )
        );

        if (count($translations ?? []) > 0) {
            if ($useAlwaysAvailable) {
                $query->where(
                    $query->expr->lOr(
                        $query->expr->in(
                            $this->handler->quoteColumn('locale', 'eztags_keyword'),
                            $translations
                        ),
                        $query->expr->lAnd(
                            $query->expr->gt(
                                $query->expr->bitAnd(
                                    $this->handler->quoteColumn('language_mask', 'eztags'),
                                    1
                                ),
                                0
                            ),
                            $query->expr->eq(
                                $this->handler->quoteColumn('main_language_id', 'eztags'),
                                $query->expr->bitAnd(
                                    $this->handler->quoteColumn('language_id', 'eztags_keyword'),
                                    -2 // -2 == PHP_INT_MAX << 1
                                )
                            )
                        )
                    )
                );
            } else {
                $query->where(
                    $query->expr->in(
                        $this->handler->quoteColumn('locale', 'eztags_keyword'),
                        $translations
                    )
                );
            }
        }

        return $query;
    }

    /**
     * Creates a select query for tag objects.
     *
     * Creates a select query with all necessary joins to fetch a complete
     * tag. Does not apply any WHERE conditions.
     */
    private function createTagFindQuery(?array $translations = null, bool $useAlwaysAvailable = true): SelectQuery
    {
        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $query = $this->handler->createSelectQuery();
        $query->select(
            // Tag
            $this->handler->aliasedColumn($query, 'id', 'eztags'),
            $this->handler->aliasedColumn($query, 'parent_id', 'eztags'),
            $this->handler->aliasedColumn($query, 'main_tag_id', 'eztags'),
            $this->handler->aliasedColumn($query, 'keyword', 'eztags'),
            $this->handler->aliasedColumn($query, 'depth', 'eztags'),
            $this->handler->aliasedColumn($query, 'path_string', 'eztags'),
            $this->handler->aliasedColumn($query, 'modified', 'eztags'),
            $this->handler->aliasedColumn($query, 'remote_id', 'eztags'),
            $this->handler->aliasedColumn($query, 'main_language_id', 'eztags'),
            $this->handler->aliasedColumn($query, 'language_mask', 'eztags'),
            // Tag keywords
            $this->handler->aliasedColumn($query, 'keyword', 'eztags_keyword'),
            $this->handler->aliasedColumn($query, 'locale', 'eztags_keyword')
        )->from(
            $this->handler->quoteTable('eztags')
        )
        // @todo: Joining with eztags_keyword is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        ->leftJoin(
            $this->handler->quoteTable('eztags_keyword'),
            $query->expr->lAnd(
                // eztags_keyword.locale is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr->eq(
                    $this->handler->quoteColumn('keyword_id', 'eztags_keyword'),
                    $this->handler->quoteColumn('id', 'eztags')
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('status', 'eztags_keyword'),
                    $query->bindValue(1, null, PDO::PARAM_INT)
                )
            )
        );

        if (count($translations ?? []) > 0) {
            if ($useAlwaysAvailable) {
                $query->where(
                    $query->expr->lOr(
                        $query->expr->in(
                            $this->handler->quoteColumn('locale', 'eztags_keyword'),
                            $translations
                        ),
                        $query->expr->lAnd(
                            $query->expr->gt(
                                $query->expr->bitAnd(
                                    $this->handler->quoteColumn('language_mask', 'eztags'),
                                    1
                                ),
                                0
                            ),
                            $query->expr->eq(
                                $this->handler->quoteColumn('main_language_id', 'eztags'),
                                $query->expr->bitAnd(
                                    $this->handler->quoteColumn('language_id', 'eztags_keyword'),
                                    -2 // -2 == PHP_INT_MAX << 1
                                )
                            )
                        )
                    )
                );
            } else {
                $query->where(
                    $query->expr->in(
                        $this->handler->quoteColumn('locale', 'eztags_keyword'),
                        $translations
                    )
                );
            }
        }

        return $query;
    }

    /**
     * Creates a select count query for tag objects.
     *
     * Creates a select query with all necessary joins to fetch a complete
     * tag. Does not apply any WHERE conditions.
     */
    private function createTagCountQuery(?array $translations = null, bool $useAlwaysAvailable = true): SelectQuery
    {
        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $query = $this->handler->createSelectQuery();
        $query->select(
            $query->alias($query->expr->count('DISTINCT eztags.id'), 'count')
        )->from(
            $this->handler->quoteTable('eztags')
        )
        // @todo: Joining with eztags_keyword is probably a VERY bad way to gather that information
        // since it creates an additional cartesian product with translations.
        ->leftJoin(
            $this->handler->quoteTable('eztags_keyword'),
            $query->expr->lAnd(
                // eztags_keyword.locale is also part of the PK but can't be
                // easily joined with something at this level
                $query->expr->eq(
                    $this->handler->quoteColumn('keyword_id', 'eztags_keyword'),
                    $this->handler->quoteColumn('id', 'eztags')
                ),
                $query->expr->eq(
                    $this->handler->quoteColumn('status', 'eztags_keyword'),
                    $query->bindValue(1, null, PDO::PARAM_INT)
                )
            )
        );

        if (count($translations ?? []) > 0) {
            if ($useAlwaysAvailable) {
                $query->where(
                    $query->expr->lOr(
                        $query->expr->in(
                            $this->handler->quoteColumn('locale', 'eztags_keyword'),
                            $translations
                        ),
                        $query->expr->lAnd(
                            $query->expr->gt(
                                $query->expr->bitAnd(
                                    $this->handler->quoteColumn('language_mask', 'eztags'),
                                    1
                                ),
                                0
                            ),
                            $query->expr->eq(
                                $this->handler->quoteColumn('main_language_id', 'eztags'),
                                $query->expr->bitAnd(
                                    $this->handler->quoteColumn('language_id', 'eztags_keyword'),
                                    -2 // -2 == PHP_INT_MAX << 1
                                )
                            )
                        )
                    )
                );
            } else {
                $query->where(
                    $query->expr->in(
                        $this->handler->quoteColumn('locale', 'eztags_keyword'),
                        $translations
                    )
                );
            }
        }

        return $query;
    }

    /**
     * Inserts keywords for tag with provided tag ID.
     */
    private function insertTagKeywords(int $tagId, array $keywords, string $mainLanguageCode, bool $alwaysAvailable): void
    {
        foreach ($keywords as $languageCode => $keyword) {
            $query = $this->handler->createInsertQuery();
            $query
                ->insertInto($this->handler->quoteTable('eztags_keyword'))
                ->set(
                    $this->handler->quoteColumn('keyword_id'),
                    $query->bindValue($tagId, null, PDO::PARAM_INT)
                )->set(
                    $this->handler->quoteColumn('language_id'),
                    $query->bindValue(
                        $this->languageHandler->loadByLanguageCode(
                            $languageCode
                        )->id + (int) ($languageCode === $mainLanguageCode && $alwaysAvailable),
                        null,
                        PDO::PARAM_INT
                    )
                )->set(
                    $this->handler->quoteColumn('keyword'),
                    $query->bindValue($keyword, null, PDO::PARAM_STR)
                )->set(
                    $this->handler->quoteColumn('locale'),
                    $query->bindValue($languageCode, null, PDO::PARAM_STR)
                )->set(
                    $this->handler->quoteColumn('status'),
                    $query->bindValue(1, null, PDO::PARAM_INT)
                );

            $query->prepare()->execute();
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
