<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;
use PDO;
use RuntimeException;

class LegacyStorage extends Gateway
{
    /**
     * Connection.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $connection;

    /**
     * Caching language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(LanguageHandler $languageHandler)
    {
        $this->languageHandler = $languageHandler;
    }

    /**
     * Sets the data storage connection to use.
     *
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $connection
     *
     * @throws \RuntimeException if $connection is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection($connection)
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if (!$connection instanceof DatabaseHandler) {
            throw new RuntimeException('Invalid connection passed');
        }

        $this->connection = $connection;
    }

    /**
     * Stores the tags in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field)
    {
        $connection = $this->getConnection();

        foreach ($field->value->externalData as $priority => $tag) {
            $insertQuery = $connection->createInsertQuery();
            $insertQuery
                ->insertInto($connection->quoteTable('eztags_attribute_link'))
                ->set(
                    $connection->quoteColumn('keyword_id'),
                    $insertQuery->bindValue($tag['id'], null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn('objectattribute_id'),
                    $insertQuery->bindValue($field->id, null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn('objectattribute_version'),
                    $insertQuery->bindValue($versionInfo->versionNo, null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn('object_id'),
                    $insertQuery->bindValue($versionInfo->contentInfo->id, null, PDO::PARAM_INT)
                )->set(
                    $connection->quoteColumn('priority'),
                    $insertQuery->bindValue($priority, null, PDO::PARAM_INT)
                );

            $insertQuery->prepare()->execute();
        }
    }

    /**
     * Gets the tags stored in the field.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData(VersionInfo $versionInfo, Field $field)
    {
        $field->value->externalData = $this->loadFieldData($field->id, $versionInfo->versionNo);
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds)
    {
        $connection = $this->getConnection();

        $query = $connection->createDeleteQuery();
        $query
            ->deleteFrom($connection->quoteTable('eztags_attribute_link'))
            ->where(
                $query->expr->lAnd(
                    $query->expr->in(
                        $connection->quoteColumn('objectattribute_id'),
                        $fieldIds
                    ),
                    $query->expr->eq(
                        $connection->quoteColumn('objectattribute_version'),
                        $query->bindValue($versionInfo->versionNo, null, PDO::PARAM_INT)
                    )
                )
            );

        $query->prepare()->execute();
    }

    /**
     * Returns the active connection.
     *
     * @throws \RuntimeException if no connection has been set, yet
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ($this->connection === null) {
            throw new RuntimeException('Missing database connection.');
        }

        return $this->connection;
    }

    /**
     * Returns the data for the given $fieldId and $versionNo.
     *
     * @param mixed $fieldId
     * @param mixed $versionNo
     *
     * @return array
     */
    protected function loadFieldData($fieldId, $versionNo)
    {
        $connection = $this->getConnection();

        $query = $connection->createSelectQuery();
        $query
            ->selectDistinct(
                // Tag
                $connection->aliasedColumn($query, 'id', 'eztags'),
                $connection->aliasedColumn($query, 'parent_id', 'eztags'),
                $connection->aliasedColumn($query, 'main_tag_id', 'eztags'),
                $connection->aliasedColumn($query, 'keyword', 'eztags'),
                $connection->aliasedColumn($query, 'depth', 'eztags'),
                $connection->aliasedColumn($query, 'path_string', 'eztags'),
                $connection->aliasedColumn($query, 'modified', 'eztags'),
                $connection->aliasedColumn($query, 'remote_id', 'eztags'),
                $connection->aliasedColumn($query, 'main_language_id', 'eztags'),
                $connection->aliasedColumn($query, 'language_mask', 'eztags'),
                // Tag keywords
                $connection->aliasedColumn($query, 'keyword', 'eztags_keyword'),
                $connection->aliasedColumn($query, 'locale', 'eztags_keyword'),
                // Tag attribute links
                $connection->aliasedColumn($query, 'priority', 'eztags_attribute_link')
            )
            ->from($connection->quoteTable('eztags'))
            ->innerJoin(
                $connection->quoteTable('eztags_attribute_link'),
                $query->expr->eq(
                    $connection->quoteColumn('id', 'eztags'),
                    $connection->quoteColumn('keyword_id', 'eztags_attribute_link')
                )
            )
            ->innerJoin(
                $connection->quoteTable('eztags_keyword'),
                $query->expr->eq(
                    $connection->quoteColumn('id', 'eztags'),
                    $connection->quoteColumn('keyword_id', 'eztags_keyword')
                )
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $connection->quoteColumn('objectattribute_id', 'eztags_attribute_link'),
                        $query->bindValue($fieldId, null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $connection->quoteColumn('objectattribute_version', 'eztags_attribute_link'),
                        $query->bindValue($versionNo, null, PDO::PARAM_INT)
                    )
                )
            )
            ->orderBy($connection->quoteColumn('priority', 'eztags_attribute_link'));

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $tagList = array();
        foreach ($rows as $row) {
            $tagId = (int) $row['eztags_id'];
            if (!isset($tagList[$tagId])) {
                $tagList[$tagId] = array();
                $tagList[$tagId]['id'] = (int) $row['eztags_id'];
                $tagList[$tagId]['parent_id'] = (int) $row['eztags_parent_id'];
                $tagList[$tagId]['main_tag_id'] = (int) $row['eztags_main_tag_id'];
                $tagList[$tagId]['keywords'] = array();
                $tagList[$tagId]['depth'] = (int) $row['eztags_depth'];
                $tagList[$tagId]['path_string'] = $row['eztags_path_string'];
                $tagList[$tagId]['modified'] = (int) $row['eztags_modified'];
                $tagList[$tagId]['remote_id'] = $row['eztags_remote_id'];
                $tagList[$tagId]['always_available'] = ((int) $row['eztags_language_mask'] & 1) ? true : false;
                $tagList[$tagId]['main_language_code'] = $this->languageHandler->load($row['eztags_main_language_id'])->languageCode;
                $tagList[$tagId]['language_codes'] = array();
            }

            if (!isset($tagList[$tagId]['keywords'][$row['eztags_keyword_locale']])) {
                $tagList[$tagId]['keywords'][$row['eztags_keyword_locale']] = $row['eztags_keyword_keyword'];
            }

            if (!in_array(array($row['eztags_keyword_locale']), $tagList[$tagId]['language_codes'])) {
                $tagList[$tagId]['language_codes'][] = $row['eztags_keyword_locale'];
            }
        }

        return array_values($tagList);
    }
}
