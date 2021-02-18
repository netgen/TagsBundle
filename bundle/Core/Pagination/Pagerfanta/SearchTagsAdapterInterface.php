<?php

namespace Netgen\TagsBundle\Core\Pagination\Pagerfanta;

interface SearchTagsAdapterInterface
{
    /**
     * @param string $searchTerm
     */
    public function setSearchTerm(string $searchTerm);

    /**
     * @param string $language
     */
    public function setLanguage(string $language);
}
