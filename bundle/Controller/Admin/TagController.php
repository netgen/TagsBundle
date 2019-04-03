<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\SearchService;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Form\Type\CopyTagsType;
use Netgen\TagsBundle\Form\Type\LanguageSelectType;
use Netgen\TagsBundle\Form\Type\MoveTagsType;
use Netgen\TagsBundle\Form\Type\TagConvertType;
use Netgen\TagsBundle\Form\Type\TagCreateType;
use Netgen\TagsBundle\Form\Type\TagMergeType;
use Netgen\TagsBundle\Form\Type\TagUpdateType;
use Pagerfanta\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\Request;
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
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * @var \Pagerfanta\Adapter\AdapterInterface
     */
    protected $tagChildrenAdapter;

    /**
     * TagController constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\API\Repository\SearchService $searchService
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     * @param \Pagerfanta\Adapter\AdapterInterface $tagChildrenAdapter
     */
    public function __construct(
        TagsService $tagsService,
        ContentTypeService $contentTypeService,
        SearchService $searchService,
        TranslatorInterface $translator,
        AdapterInterface $tagChildrenAdapter
    ) {
        $this->tagsService = $tagsService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->translator = $translator;
        $this->tagChildrenAdapter = $tagChildrenAdapter;
    }

    /**
     * Rendering a view which shows tag or synonym details.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTagAction(Request $request, Tag $tag = null)
    {
        $this->denyAccessUnlessGranted('ez:tags:read');

        $data = [];

        if (!$tag instanceof Tag || !$tag->isSynonym()) {
            $configResolver = $this->getConfigResolver();

            $currentPage = (int) $request->query->get('page');
            $pager = $this->createPager(
                $this->tagChildrenAdapter,
                $currentPage,
                $configResolver->getParameter('admin.children_limit', 'eztags'),
                $tag
            );

            $data += [
                'childrenTags' => $pager,
            ];
        }

        if (!$tag instanceof Tag) {
            return $this->render(
                '@NetgenTags/admin/tag/dashboard.html.twig',
                $data
            );
        }

        $data += [
            'tag' => $tag,
            'latestContent' => $this->tagsService->getRelatedContent($tag, 0, 10),
        ];

        if (!$tag->isSynonym()) {
            $data += [
                'synonyms' => $this->tagsService->loadTagSynonyms($tag),
                'subTreeLimitations' => $this->getSubtreeLimitations($tag),
            ];
        }

        return $this->render(
            '@NetgenTags/admin/tag/show.html.twig',
            $data
        );
    }

    /**
     * This method is called for add new tag action without selected language.
     * It renders a form to select language for the keyword of new tag.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $parentTag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addTagSelectAction(Request $request, Tag $parentTag = null)
    {
        $this->denyAccessUnlessGranted('ez:tags:add', $parentTag);

        $availableLanguages = $this->getConfigResolver()->getParameter('languages');
        if (count($availableLanguages) === 1) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_add',
                [
                    'parentId' => $parentTag !== null ? $parentTag->id : 0,
                    'languageCode' => $availableLanguages[0],
                ]
            );
        }

        $form = $this->createForm(
            LanguageSelectType::class,
            null,
            [
                'action' => $request->getPathInfo(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_add',
                [
                    'parentId' => $parentTag !== null ? $parentTag->id : 0,
                    'languageCode' => $form->getData()['languageCode'],
                ]
            );
        }

        return $this->render(
            '@NetgenTags/admin/tag/select_translation.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * This method renders view with a form for adding new tag.
     * After form is being submitted, it stores new tag and redirects to it.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $languageCode
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $parentTag
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addTagAction(Request $request, $languageCode, Tag $parentTag = null)
    {
        $this->denyAccessUnlessGranted('ez:tags:add', $parentTag);

        $tagCreateStruct = $this->tagsService->newTagCreateStruct(
            $parentTag ? $parentTag->id : 0,
            $languageCode
        );

        $form = $this->createForm(
            TagCreateType::class,
            $tagCreateStruct,
            [
                'action' => $request->getPathInfo(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTag = $this->tagsService->createTag($form->getData());

            $this->addFlashMessage('success', 'tag_added', ['%tagKeyword%' => $newTag->keyword]);

            return $this->redirectToTag($newTag);
        }

        return $this->render(
            '@NetgenTags/admin/tag/add.html.twig',
            [
                'form' => $form->createView(),
                'parentTag' => $parentTag,
            ]
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
        $this->denyAccessUnlessGranted('ez:tags:edit' . ($tag->isSynonym() ? 'synonym' : ''));

        $availableLanguages = $this->getConfigResolver()->getParameter('languages');
        if (count($availableLanguages) === 1) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_update',
                [
                    'tagId' => $tag->id,
                    'languageCode' => $availableLanguages[0],
                ]
            );
        }

        $form = $this->createForm(
            LanguageSelectType::class,
            null,
            [
                'action' => $request->getPathInfo(),
                'tag' => $tag,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_update',
                [
                    'tagId' => $tag->id,
                    'languageCode' => $form->getData()['languageCode'],
                ]
            );
        }

        return $this->render(
            '@NetgenTags/admin/tag/select_translation.html.twig',
            [
                'form' => $form->createView(),
                'tag' => $tag,
            ]
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
        $this->denyAccessUnlessGranted('ez:tags:edit' . ($tag->isSynonym() ? 'synonym' : ''));

        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();
        $tagUpdateStruct->remoteId = $tag->remoteId;
        $tagUpdateStruct->alwaysAvailable = $tag->alwaysAvailable;

        foreach ($tag->keywords as $keywordLanguageCode => $keyword) {
            $tagUpdateStruct->setKeyword($keyword, $keywordLanguageCode);
        }

        $form = $this->createForm(
            TagUpdateType::class,
            $tagUpdateStruct,
            [
                'action' => $request->getPathInfo(),
                'languageCode' => $languageCode,
                'tag' => $tag,
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updatedTag = $this->tagsService->updateTag($tag, $form->getData());

            $this->addFlashMessage('success', 'tag_updated', ['%tagKeyword%' => $updatedTag->keyword]);

            return $this->redirectToTag($updatedTag);
        }

        return $this->render(
            '@NetgenTags/admin/tag/update.html.twig',
            [
                'form' => $form->createView(),
                'tag' => $tag,
            ]
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
        $this->denyAccessUnlessGranted('ez:tags:delete' . ($tag->isSynonym() ? 'synonym' : ''));

        if ($request->request->has('DeleteTagButton')) {
            if (!$this->isCsrfTokenValid('eztags_admin', $request->request->get('_csrf_token'))) {
                $this->addFlashMessage('errors', 'invalid_csrf_token');

                return $this->redirectToTag($tag);
            }

            $this->tagsService->deleteTag($tag);

            $this->addFlashMessage('success', 'tag_deleted', ['%tagKeyword%' => $tag->keyword]);

            return $this->redirectToTag();
        }

        return $this->render(
            '@NetgenTags/admin/tag/delete.html.twig',
            [
                'tag' => $tag,
            ]
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
        $this->denyAccessUnlessGranted('ez:tags:merge');

        $form = $this->createForm(
            TagMergeType::class,
            null,
            [
                'tag' => $tag,
                'action' => $request->getPathInfo(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sourceTag = $this->tagsService->loadTag($form->getData()['mainTag']);

            $this->tagsService->mergeTags($tag, $sourceTag);

            $this->addFlashMessage(
                'success',
                'tag_merged',
                [
                    '%tagKeyword%' => $tag->keyword,
                    '%sourceTagKeyword%' => $sourceTag->keyword,
                ]
            );

            return $this->redirectToTag($sourceTag);
        }

        return $this->render(
            '@NetgenTags/admin/tag/merge.html.twig',
            [
                'form' => $form->createView(),
                'tag' => $tag,
            ]
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
        $this->denyAccessUnlessGranted('ez:tags:makesynonym');

        $form = $this->createForm(
            TagConvertType::class,
            null,
            [
                'tag' => $tag,
                'action' => $request->getPathInfo(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainTag = $this->tagsService->loadTag($form->getData()['mainTag']);

            $this->tagsService->convertToSynonym($tag, $mainTag);

            $this->addFlashMessage(
                'success',
                'tag_converted',
                [
                    '%tagKeyword%' => $tag->keyword,
                    '%mainTagKeyword%' => $mainTag->keyword,
                ]
            );

            return $this->redirectToTag($mainTag);
        }

        return $this->render(
            '@NetgenTags/admin/tag/convert.html.twig',
            [
                'form' => $form->createView(),
                'tag' => $tag,
            ]
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
        $this->denyAccessUnlessGranted('ez:tags:edit' . ($tag->isSynonym() ? 'synonym' : ''));

        if (!$this->isCsrfTokenValid('eztags_admin', $request->request->get('_csrf_token'))) {
            $this->addFlashMessage('errors', 'invalid_csrf_token');

            return $this->redirectToTag($tag);
        }

        $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();

        if ($request->request->has('RemoveTranslationButton')) {
            if (empty($request->request->get('Locale'))) {
                $this->addFlashMessage('errors', 'no_translation_selected');

                return $this->redirectToTag($tag);
            }

            $locales = $request->request->get('Locale');

            $newKeywords = $tag->keywords;

            foreach ($locales as $locale) {
                if (!isset($newKeywords[$locale])) {
                    $this->addFlashMessage('errors', 'no_translation', ['%locale%' => $locale]);
                } elseif ($locale === $tag->mainLanguageCode) {
                    $this->addFlashMessage('errors', 'main_translation');
                } else {
                    unset($newKeywords[$locale]);

                    $tagUpdateStruct = $this->tagsService->newTagUpdateStruct();

                    foreach ($newKeywords as $languageCode => $keyword) {
                        $tagUpdateStruct->setKeyword($keyword, $languageCode);
                    }

                    $this->tagsService->updateTag($tag, $tagUpdateStruct);

                    $this->addFlashMessage('success', 'translation_removed');
                }
            }
        } elseif ($request->request->has('UpdateMainTranslationButton')) {
            $newMainTranslation = $request->request->get('MainLocale');

            if (!in_array($newMainTranslation, $tag->languageCodes, true)) {
                $this->addFlashMessage('errors', 'no_translation', ['%locale%', $newMainTranslation]);
            } else {
                $tagUpdateStruct->mainLanguageCode = $newMainTranslation;
                $this->tagsService->updateTag($tag, $tagUpdateStruct);

                $this->addFlashMessage('success', 'main_translation_set', ['%locale%' => $newMainTranslation]);
            }
        } elseif ($request->request->has('UpdateAlwaysAvailableButton')) {
            $tagUpdateStruct->alwaysAvailable = (bool) $request->request->get('AlwaysAvailable');
            $this->tagsService->updateTag($tag, $tagUpdateStruct);

            $this->addFlashMessage('success', 'always_available_set');
        }

        return $this->redirectToTag($tag);
    }

    /**
     * Action that handles redirection for actions on multiple tags children.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function childrenAction(Request $request, Tag $tag = null)
    {
        if (empty($request->request->get('Tags'))) {
            $this->addFlashMessage('errors', 'no_selected_tags');

            return $this->redirectToTag($tag);
        }

        $this->get('session')->set(
            'ngtags_tag_ids',
            $request->request->get('Tags')
        );

        if ($request->request->has('MoveTagsAction')) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_move_tags',
                [
                    'parentId' => $tag !== null ? $tag->id : 0,
                ]
            );
        }

        if ($request->request->has('CopyTagsAction')) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_copy_tags',
                [
                    'parentId' => $tag !== null ? $tag->id : 0,
                ]
            );
        }

        if ($request->request->has('DeleteTagsAction')) {
            return $this->redirectToRoute(
                'netgen_tags_admin_tag_delete_tags',
                [
                    'parentId' => $tag !== null ? $tag->id : 0,
                ]
            );
        }

        return $this->redirect($request->getPathInfo());
    }

    /**
     * This method is called from a form containing all children tags of a tag.
     * It shows a confirmation view.
     * If form has been submitted, it moves selected children and all its children tags to a new parent.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $parentTag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function moveTagsAction(Request $request, Tag $parentTag = null)
    {
        $this->denyAccessUnlessGranted('ez:tags:edit');

        $tagIds = $request->request->has('Tags') ?
            $request->request->get('Tags') :
            $this->get('session')->get('ngtags_tag_ids');

        if (empty($tagIds)) {
            return $this->redirectToTag($parentTag);
        }

        $tags = [];
        foreach ($tagIds as $tagId) {
            $tags[] = $this->tagsService->loadTag($tagId);
        }

        $form = $this->createForm(
            MoveTagsType::class,
            [
                'parentTag' => $parentTag instanceof Tag ? $parentTag->id : 0,
            ],
            [
                'tags' => $tags,
                'action' => $request->getPathInfo(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $newParentTag = $this->tagsService->loadTag($form->getData()['parentTag']);
            } catch (NotFoundException $exception) {
                $newParentTag = null;
            }

            foreach ($tags as $tagObject) {
                $this->tagsService->moveSubtree($tagObject, $newParentTag);
            }

            $this->addFlashMessage('success', 'tags_moved');

            return $this->redirectToTag($parentTag);
        }

        return $this->render(
            '@NetgenTags/admin/tag/move_tags.html.twig',
            [
                'parentTag' => $parentTag ?: null,
                'tags' => $tags,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * This method is called from a form containing all children tags of a tag.
     * It shows a confirmation view.
     * If form has been submitted, it copies selected children and all its children tags to a new parent.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $parentTag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function copyTagsAction(Request $request, Tag $parentTag = null)
    {
        $this->denyAccessUnlessGranted('ez:tags:read');

        $tagIds = $request->request->has('Tags') ?
            $request->request->get('Tags') :
            $this->get('session')->get('ngtags_tag_ids');

        if (empty($tagIds)) {
            return $this->redirectToTag($parentTag);
        }

        $tags = [];
        foreach ($tagIds as $tagId) {
            $tags[] = $this->tagsService->loadTag($tagId);
        }

        $form = $this->createForm(
            CopyTagsType::class,
            [
                'parentTag' => $parentTag instanceof Tag ? $parentTag->id : 0,
            ],
            [
                'tags' => $tags,
                'action' => $request->getPathInfo(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $newParentTag = $this->tagsService->loadTag($form->getData()['parentTag']);
            } catch (NotFoundException $exception) {
                $newParentTag = null;
            }

            foreach ($tags as $tagObject) {
                $this->tagsService->copySubtree($tagObject, $newParentTag);
            }

            $this->addFlashMessage('success', 'tags_copied');

            return $this->redirectToTag($parentTag);
        }

        return $this->render(
            '@NetgenTags/admin/tag/copy_tags.html.twig',
            [
                'parentTag' => $parentTag ?: null,
                'tags' => $tags,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * This method is called from a form containing all children tags of a tag.
     * It shows a confirmation view.
     * If form has been submitted, it deletes selected children tags.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $parentTag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteTagsAction(Request $request, Tag $parentTag = null)
    {
        $this->denyAccessUnlessGranted('ez:tags:delete');

        $tagIds = $request->request->has('Tags') ?
            $request->request->get('Tags') :
            $this->get('session')->get('ngtags_tag_ids');

        if (empty($tagIds)) {
            return $this->redirectToTag($parentTag);
        }

        $tags = [];
        foreach ($tagIds as $tagId) {
            $tags[] = $this->tagsService->loadTag($tagId);
        }

        if ($request->request->has('DeleteTagsButton')) {
            if (!$this->isCsrfTokenValid('eztags_admin', $request->request->get('_csrf_token'))) {
                $this->addFlashMessage('errors', 'invalid_csrf_token');

                return $this->redirectToTag($parentTag);
            }

            foreach ($tags as $tagObject) {
                $this->tagsService->deleteTag($tagObject);
            }

            $this->addFlashMessage('success', 'tags_deleted');

            return $this->redirectToTag($parentTag);
        }

        return $this->render(
            '@NetgenTags/admin/tag/delete_tags.html.twig',
            [
                'parentTag' => $parentTag ?: null,
                'tags' => $tags,
            ]
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
        $result = [];

        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
                    if ($fieldDefinition->fieldTypeIdentifier === 'eztags') {
                        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
                        if (
                            !empty($validatorConfiguration['TagsValueValidator']['subTreeLimit']) &&
                            $validatorConfiguration['TagsValueValidator']['subTreeLimit'] === $tag->id
                        ) {
                            $result[] = [
                                'contentTypeId' => $contentType->id,
                                'attributeIdentifier' => $fieldDefinition->identifier,
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }
}
