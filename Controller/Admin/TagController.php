<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Content\Query\Criterion\TagId;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\HttpFoundation\Request;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Symfony\Component\Translation\TranslatorInterface;

class TagController extends Controller
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
     * @var \eZ\Publish\API\Repository\SearchService
     */
    protected $searchService;

    /**
     * @var array
     */
    protected $languages;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * TagController constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TagsService $tagsService, ContentTypeService $contentTypeService, SearchService $searchService, TranslatorInterface $translator)
    {
        $this->tagsService = $tagsService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->translator = $translator;
    }

    /**
     * Setter method for array with languages.
     *
     * @param array|null $languages
     */
    public function setLanguages(array $languages = null)
    {
        $this->languages = $languages;
    }

    /**
     * Rendering a view which shows tag or synonym details.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTagAction(Tag $tag)
    {
        $data = array(
            'tag' => $tag,
            'latestContent' => $this->getLatestContent($tag, 10),
        );

        if (!$tag->isSynonym()) {
            $data += array(
                'synonyms' => $this->tagsService->loadTagSynonyms($tag, 0, 10),
                'childrenTags' => $this->tagsService->loadTagChildren($tag, 0, 10),
                'subTreeLimitations' => $this->getSubtreeLimitations($tag),
            );
        }

        return $this->render(
            $tag->isSynonym() ?
                'NetgenTagsBundle:admin/synonym:show.html.twig' :
                'NetgenTagsBundle:admin/tag:show.html.twig',
            $data
        );
    }

    /**
     * This method is called for add new tag action without selected language.
     * It renders a form to select language for the keyword of new tag.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string $parentId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addTagSelectAction(Request $request, $parentId)
    {
        $form = $this->createForm(
            'Netgen\TagsBundle\Form\Type\LanguageSelectType',
            null,
            array(
                'languages' => $this->languages,
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_add',
                array(
                    'tagId' => $parentId,
                    'languageCode' => $form->getData()['languageCode'],
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/tag:select_translation.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * This method renders view with a form for adding new tag.
     * After form is being submitted, it stores new tag and redirects to it.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $languageCode
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addTagAction(Request $request, $languageCode, Tag $tag = null)
    {
        if ($tag === null) {
            $tagCreateStruct = $this->tagsService->newTagCreateStruct(0, $languageCode);
        } else {
            $tagCreateStruct = $this->tagsService->newTagCreateStruct($tag->id, $languageCode);
        }

        $form = $this->createForm(
            'Netgen\TagsBundle\Form\Type\TagCreateType',
            $tagCreateStruct,
            array(
                'languageCode' => $languageCode,
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTag = $this->tagsService->createTag($form->getData());

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'tag.add.success',
                    array(
                        '%tagKeyword%' => $newTag->keyword,
                    ),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $newTag->id,
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/tag:add.html.twig',
            array(
                'form' => $form->createView(),
                'parentTag' => $tag,
            )
        );
    }

    /**
     * This method is called for update tag or synonym action without selected language.
     * It renders a form to select language for which the user wants to update keyword of the tag or synonym.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateTagSelectAction(Request $request, Tag $tag)
    {
        $form = $this->createForm(
            'Netgen\TagsBundle\Form\Type\LanguageSelectType',
            null,
            array(
                'languages' => $this->languages,
                'tag' => $tag,
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_update',
                array(
                    'tagId' => $tag->id,
                    'languageCode' => $form->getData()['languageCode'],
                )
            );
        }

        return $this->render(
            $tag->isSynonym() ?
                'NetgenTagsBundle:admin/synonym:select_translation.html.twig' :
                'NetgenTagsBundle:admin/tag:select_translation.html.twig',
            array(
                'form' => $form->createView(),
                'tag' => $tag,
            )
        );
    }

    /**
     * This method renders view with a form for updating a tag or synonym.
     * After form is being submitted, it updates the tag or synonym and redirects to it.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param string $languageCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateTagAction(Request $request, Tag $tag, $languageCode)
    {
        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();
        $tagUpdateStruct->remoteId = $tag->remoteId;
        $tagUpdateStruct->alwaysAvailable = $tag->alwaysAvailable;

        foreach ($tag->keywords as $keywordLanguageCode => $keyword) {
            $tagUpdateStruct->setKeyword($keyword, $keywordLanguageCode);
        }

        $form = $this->createForm(
            'Netgen\TagsBundle\Form\Type\TagUpdateType',
            $tagUpdateStruct,
            array(
                'languageCode' => $languageCode,
                'tag' => $tag,
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updatedTag = $this->tagsService->updateTag($tag, $form->getData());

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'tag.edit.success',
                    array(
                        '%tagKeyword%' => $updatedTag->keyword,
                    ),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $updatedTag->id,
                )
            );
        }

        return $this->render(
            $tag->isSynonym() ?
                'NetgenTagsBundle:admin/synonym:update.html.twig' :
                'NetgenTagsBundle:admin/tag:update.html.twig',
            array(
                'form' => $form->createView(),
                'tag' => $tag,
            )
        );
    }

    /**
     * This method is called for delete tag or synonym action.
     * It shows a confirmation view.
     * If form has been submitted, it deletes the tag or synonym and redirects to dashboard.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteTagAction(Request $request, Tag $tag)
    {
        if ($request->request->has('DeleteTagButton')) {
            $this->tagsService->deleteTag($tag);

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    $tag->isSynonym() ?
                        'synonym.delete.success' :
                        'tag.delete.success',
                    array(
                        '%tagKeyword%' => $tag->keyword,
                    ),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_dashboard_index'
            );
        }

        return $this->render(
            $tag->isSynonym() ?
                'NetgenTagsBundle:admin/synonym:delete.html.twig' :
                'NetgenTagsBundle:admin/tag:delete.html.twig',
            array(
                'tag' => $tag,
            )
        );
    }

    /**
     * This method is called for merge tag action.
     * It shows a confirmation view.
     * If form has been submitted, it merges tags and redirects to source tag.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mergeTagAction(Request $request, Tag $tag)
    {
        $form = $this->createForm('Netgen\TagsBundle\Form\Type\TagMergeType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sourceTag = $this->tagsService->loadTag($form->getData()['mainTag']);

            $this->tagsService->mergeTags($tag, $sourceTag);

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'tag.merge.success',
                    array(
                        '%tagKeyword%' => $tag->keyword,
                        '%sourceTagKeyword%' => $sourceTag->keyword,
                    ),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $sourceTag->id,
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/tag:merge.html.twig',
            array(
                'form' => $form->createView(),
                'tag' => $tag,
            )
        );
    }

    /**
     * This method is called for convert tag to synonym action.
     * It shows a confirmation view.
     * If form has been submitted, it converts the tag to synonym and redirects to newly created synonym.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function convertToSynonymAction(Request $request, Tag $tag)
    {
        $form = $this->createForm('Netgen\TagsBundle\Form\Type\TagConvertType');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainTag = $this->tagsService->loadTag($form->getData()['mainTag']);

            $this->tagsService->convertToSynonym($tag, $mainTag);

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'tag.convert.success',
                    array(
                        '%tagKeyword%' => $tag->keyword,
                        '%mainTagKeyword%' => $mainTag->keyword,
                    ),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $mainTag->id,
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/tag:convert.html.twig',
            array(
                'form' => $form->createView(),
                'tag' => $tag,
            )
        );
    }

    /**
     * This method is called from a form with each tag or synonym's translations table.
     * An action is defined with one of the three buttons in form which can be pressed.
     * It can remove selected translation, set it as main language, or set AlwaysAvailable property of a tag.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function translationAction(Request $request, Tag $tag)
    {
        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();

        if ($request->request->has('RemoveTranslationButton')) {
            $locales = $request->request->get('Locale');

            $newKeywords = $tag->keywords;

            foreach ($locales as $locale) {
                if (!isset($newKeywords[$locale])) {
                    $this->addFlash(
                        'errorMessages',
                        $this->translator->trans(
                            'tag.translation.no_translation',
                            array(
                                '%locale%' => $locale,
                            ),
                            'eztags_admin'
                        )
                    );
                } elseif ($locale === $tag->mainLanguageCode) {
                    $this->addFlash(
                        'errorMessages',
                        $this->translator->trans(
                            'tag.translation.is_main',
                            array(),
                            'eztags_admin'
                        )
                    );
                } else {
                    unset($newKeywords[$locale]);

                    foreach ($newKeywords as $languageCode => $keyword) {
                        $tagUpdateStruct->setKeyword($keyword, $languageCode);
                    }

                    $this->tagsService->updateTag($tag, $tagUpdateStruct);

                    $this->addFlash(
                        'successMessages',
                        $this->translator->trans(
                            'tag.translation.removed',
                            array(
                                '%locale%' => $locale,
                            ),
                            'eztags_admin'
                        )
                    );
                }
            }
        } elseif ($request->request->has('UpdateMainTranslationButton')) {
            $newMainTranslation = $request->request->get('MainLocale');

            if (!in_array($newMainTranslation, $tag->languageCodes)) {
                $this->addFlash(
                    'errorMessages',
                    $this->translator->trans(
                        'tag.translation.no_translation',
                        array(
                            '%locale%' => $newMainTranslation,
                        ),
                        'eztags_admin'
                    )
                );
            } else {
                $tagUpdateStruct->mainLanguageCode = $newMainTranslation;
                $this->tagsService->updateTag($tag, $tagUpdateStruct);

                $this->addFlash(
                    'successMessages',
                    $this->translator->trans(
                        'tag.translation.new_main',
                        array(
                            '%locale%' => $newMainTranslation,
                        ),
                        'eztags_admin'
                    )
                );
            }
        } elseif ($request->request->has('UpdateAlwaysAvailableButton')) {
            $tagUpdateStruct->alwaysAvailable = (bool) $request->request->get('AlwaysAvailable');
            $this->tagsService->updateTag($tag, $tagUpdateStruct);

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'tag.translation.always_available',
                    array(),
                    'eztags_admin'
                )
            );
        }

        return $this->redirectToRoute(
            'netgen_tags_admin_tag_show',
            array(
                'tagId' => $tag->id,
            )
        );
    }

    /**
     * This method is called from a form containing all children tags of a tag.
     * It shows a confirmation view.
     * If form has been submitted, it moves selected children and all its subchildren tags to a new parent.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function moveChildrenAction(Request $request, Tag $tag)
    {
        if (!$request->request->has('Tags') || $request->request->get('Tags') === null) {
            $this->addFlash(
                'errorMessages',
                $this->translator->trans(
                    'tag.children.no_selected_tags',
                    array(),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $tag->id,
                )
            );
        }

        $tagIds = $request->request->get('Tags');

        $tags = array();

        foreach ($tagIds as $tagId) {
            $tags[] = $this->tagsService->loadTag($tagId);
        }

        if ($request->request->has('MoveTagsButton')) {
            $newParentTag = $this->tagsService->loadTag($request->request->get('ParentTagId'));

            foreach ($tags as $_tag) {
                $this->tagsService->moveSubtree($_tag, $newParentTag);
            }

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'tag.move_tags.success',
                    array(),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $tag->id,
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/tag:move_tags.html.twig',
            array(
                'parentTag' => $tag,
                'tags' => $tags,
            )
        );
    }

    /**
     * This method is called from a form containing all children tags of a tag.
     * It shows a confirmation view.
     * If form has been submitted, it deletes selected children tags.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteChildrenAction(Request $request, Tag $tag)
    {
        if (!$request->request->has('Tags') || $request->request->get('Tags') === null) {
            $this->addFlash(
                'errorMessages',
                $this->translator->trans(
                    'tag.children.no_selected_tags',
                    array(),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $tag->id,
                )
            );
        }

        $tagIds = $request->request->get('Tags');

        $tags = array();

        foreach ($tagIds as $tagId) {
            $tags[] = $this->tagsService->loadTag($tagId);
        }

        if ($request->request->has('DeleteTagsButton')) {
            foreach ($tags as $_tag) {
                $this->tagsService->deleteTag($_tag);
            }

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'tag.delete_tags.success',
                    array(),
                    'eztags_admin'
                )
            );

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $tag->id,
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/tag:delete_tags.html.twig',
            array(
                'parentTag' => $tag,
                'tags' => $tags,
            )
        );
    }

    /**
     * Returns an array with subtree limitations for given tag.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return array
     */
    protected function getSubtreeLimitations(Tag $tag)
    {
        $result = array();

        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    if ($fieldDefinition->fieldTypeIdentifier === 'eztags') {
                        if ($fieldDefinition->getFieldSettings()['subTreeLimit'] === $tag->id) {
                            $result[] = array(
                                'contentTypeId' => $contentType->id,
                                'attributeIdentifier' => $fieldDefinition->identifier,
                            );
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Return latest content using eZ search service.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param int $limit
     *
     * @return array
     */
    protected function getLatestContent(Tag $tag, $limit)
    {
        $query = new Query();

        $criteria = array(
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new TagId($tag->id),
        );

        $query->filter = new Criterion\LogicalAnd($criteria);
        $query->limit = $limit;

        $query->sortClauses = array(
            new Query\SortClause\DateModified(Query::SORT_DESC),
        );

        $searchResult = $this->searchService->findContent($query);

        return array_map(
            function (SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResult->searchHits
        );
    }
}
