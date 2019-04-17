<?php

namespace Netgen\TagsBundle\Form\Type;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Search\Facet\ContentTypeFacet;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeFilterType extends AbstractType
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
     * ContentTypeFilterType constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     */
    public function __construct(TagsService $tagsService, ContentTypeService $contentTypeService)
    {
        $this->tagsService = $tagsService;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('tag', null)
            ->setRequired('tag')
            ->setAllowedTypes('tag', Tag::class);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'content_types',
                ChoiceType::class,
                array(
                    'choices' => $this->getContentTypeOptions($options['tag']),
                    'label' => 'tag.related_content.filter.content_type',
                    'expanded' => false,
                    'multiple' => true,
                )
            );
    }

    /**
     * Extracts content type options from facets.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return array
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function getContentTypeOptions(Tag $tag)
    {
        $facets = $this->tagsService->getRelatedContentTypeFacets($tag);

        $options = array();

        foreach ($facets as $facet) {
            if (!$facet instanceof ContentTypeFacet) {
                continue;
            }

            foreach ($facet->entries as $contentTypeId => $count) {
                $contentType = $this->contentTypeService->loadContentType($contentTypeId);
                $value = $contentType->getName()." (".$count.")";

                $options[$value] = $contentType->identifier;
            }
        }

        return $options;
    }
}
