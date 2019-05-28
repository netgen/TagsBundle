<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Query\FacetBuilder\ContentTypeFacetBuilder;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\ContentTypeFacet;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Core\Search\RelatedContent\SortService;
use Netgen\TagsBundle\Exception\FacetingNotSupportedException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelatedContentFilterType extends AbstractType
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var \Netgen\TagsBundle\Core\Search\RelatedContent\SortService
     */
    protected $sortService;

    /**
     * ContentTypeFilterType constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \Netgen\TagsBundle\Core\Search\RelatedContent\SortService $sortService
     */
    public function __construct(TagsService $tagsService, ContentTypeService $contentTypeService, SortService $sortService)
    {
        $this->tagsService = $tagsService;
        $this->contentTypeService = $contentTypeService;
        $this->sortService = $sortService;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('tag')
            ->setAllowedTypes('tag', Tag::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content_types',
                ChoiceType::class,
                [
                    'choices' => $this->getContentTypeOptions($options['tag']),
                    'label' => 'tag.related_content.filter.content_type',
                    'expanded' => false,
                    'multiple' => true,
                    'required' => false,
                ]
            )->add(
                'sort',
                ChoiceType::class,
                [
                    'choices' => $this->getSortOptions(),
                    'label' => 'tag.related_content.filter.sort',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                ]
            );
    }

    /**
     * Extracts content type options from facets.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return array
     */
    protected function getContentTypeOptions(Tag $tag)
    {
        try {
            return $this->getContentTypeOptionsFromFacets($tag);
        } catch (FacetingNotSupportedException $e) {
        }

        return $this->getAllContentTypeOptions();
    }

    /**
     * Extracts options for content type filter form select from facets.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @throws \Netgen\TagsBundle\Exception\FacetingNotSupportedException
     *
     * @return array
     */
    protected function getContentTypeOptionsFromFacets(Tag $tag)
    {
        $facetBuilders = [
            new ContentTypeFacetBuilder(
                [
                    'name' => 'content_type',
                    'minCount' => 1,
                ]
            ),
        ];

        $facets = $this->tagsService->getRelatedContentFacets($tag, $facetBuilders);

        $options = [];
        foreach ($facets as $facet) {
            if (!$facet instanceof ContentTypeFacet) {
                continue;
            }

            foreach ($facet->entries as $contentTypeId => $count) {
                try {
                    $contentType = $this->contentTypeService->loadContentType($contentTypeId);
                    $value = $contentType->getName() . ' (' . $count . ')';

                    $options[$value] = $contentType->identifier;
                } catch (NotFoundException $e) {
                }
            }
        }

        return $options;
    }

    /**
     * Get all content type options grouped by content type groups.
     *
     * @return array
     */
    protected function getAllContentTypeOptions()
    {
        $groups = $this->contentTypeService->loadContentTypeGroups();
        $options = [];

        foreach ($groups as $group) {
            $contentTypes = $this->contentTypeService->loadContentTypes($group);
            $groupOptions = [];

            foreach ($contentTypes as $contentType) {
                $groupOptions[$contentType->getName()] = $contentType->identifier;
            }

            $options[$group->identifier] = $groupOptions;
        }

        return $options;
    }

    /**
     * Prepares sort options for form.
     *
     * @return array
     */
    protected function getSortOptions()
    {
        $sortOptions = $this->sortService->getSortOptions();

        $options = [];
        foreach ($sortOptions as $sortOption) {
            $label = 'tag.related_content.filter.sort.' . $sortOption;
            $options[$label] = $sortOption;
        }

        return $options;
    }
}
