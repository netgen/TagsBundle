<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

interface SearchTagsAdapterInterface
{
    /**
     * @param string $searchText
     */
    public function setSearchText($searchText);

    /**
     * @param string $language
     */
    public function setLanguage($language);
}
