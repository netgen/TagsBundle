<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;

use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;
use PDO;

class LegacyStorage extends Gateway
{
    /**
     * Connection.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $dbHandler;

    /**
     * Caching language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(DatabaseHandler $dbHandler, LanguageHandler $languageHandler)
    {
        $this->dbHandler = $dbHandler;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Stores the tags in the database based on the given field data.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function storeFieldData(VersionInfo $versionInfo, Field $field)
    {
        foreach ($field->value->externalData as $priority => $tag) {
            $insertQuery = $this->dbHandler->createInsertQuery();
            $insertQuery
                ->insertInto($this->dbHandler->quoteTable('eztags_attribute_link'))
                ->set(
                    $this->dbHandler->quoteColumn('keyword_id'),
                    $insertQuery->bindValue($tag['id'], null, PDO::PARAM_INT)
                )->set(
                    $this->dbHandler->quoteColumn('objectattribute_id'),
                    $insertQuery->bindValue($field->id, null, PDO::PARAM_INT)
                )->set(
                    $this->dbHandler->quoteColumn('objectattribute_version'),
                    $insertQuery->bindValue($versionInfo->versionNo, null, PDO::PARAM_INT)
                )->set(
                    $this->dbHandler->quoteColumn('object_id'),
                    $insertQuery->bindValue($versionInfo->contentInfo->id, null, PDO::PARAM_INT)
                )->set(
                    $this->dbHandler->quoteColumn('priority'),
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
        $query = $this->dbHandler->createDeleteQuery();
        $query
            ->deleteFrom($this->dbHandler->quoteTable('eztags_attribute_link'))
            ->where(
                $query->expr->lAnd(
                    $query->expr->in(
                        $this->dbHandler->quoteColumn('objectattribute_id'),
                        $fieldIds
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('objectattribute_version'),
                        $query->bindValue($versionInfo->versionNo, null, PDO::PARAM_INT)
                    )
                )
            );

        $query->prepare()->execute();
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
        $query = $this->dbHandler->createSelectQuery();
        $query
            ->selectDistinct(
                // Tag
                $this->dbHandler->aliasedColumn($query, 'id', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'parent_id', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'main_tag_id', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'keyword', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'depth', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'path_string', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'modified', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'remote_id', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'main_language_id', 'eztags'),
                $this->dbHandler->aliasedColumn($query, 'language_mask', 'eztags'),
                // Tag keywords
                $this->dbHandler->aliasedColumn($query, 'keyword', 'eztags_keyword'),
                $this->dbHandler->aliasedColumn($query, 'locale', 'eztags_keyword'),
                // Tag attribute links
                $this->dbHandler->aliasedColumn($query, 'priority', 'eztags_attribute_link')
            )
            ->from($this->dbHandler->quoteTable('eztags'))
            ->innerJoin(
                $this->dbHandler->quoteTable('eztags_attribute_link'),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'eztags'),
                    $this->dbHandler->quoteColumn('keyword_id', 'eztags_attribute_link')
                )
            )
            ->innerJoin(
                $this->dbHandler->quoteTable('eztags_keyword'),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'eztags'),
                    $this->dbHandler->quoteColumn('keyword_id', 'eztags_keyword')
                )
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('objectattribute_id', 'eztags_attribute_link'),
                        $query->bindValue($fieldId, null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $this->dbHandler->quoteColumn('objectattribute_version', 'eztags_attribute_link'),
                        $query->bindValue($versionNo, null, PDO::PARAM_INT)
                    )
                )
            )
            ->orderBy($this->dbHandler->quoteColumn('priority', 'eztags_attribute_link'));

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

            if (!in_array(array($row['eztags_keyword_locale']), $tagList[$tagId]['language_codes'], true)) {
                $tagList[$tagId]['language_codes'][] = $row['eztags_keyword_locale'];
            }
        }

        return array_values($tagList);
    }
}
