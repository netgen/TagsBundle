<?php

namespace Netgen\TagsBundle\Controller\Admin;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Form\Type\LanguageSelectType;
use Netgen\TagsBundle\Form\Type\SynonymCreateType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class SynonymController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * SynonymController constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \Symfony\Component\Translation\TranslatorInterface $translator
     */
    public function __construct(TagsService $tagsService, TranslatorInterface $translator)
    {
        $this->tagsService = $tagsService;
        $this->translator = $translator;
    }

    /**
     * This method is called for add new synonym action without selected language.
     * It renders a form to select language for the keyword of new synonym.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string $mainTagId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addSynonymSelectAction(Request $request, $mainTagId)
    {
        $this->denyAccessUnlessGranted('ez:tags:addsynonym');

        $availableLanguages = $this->getConfigResolver()->getParameter('languages');
        if (count($availableLanguages) === 1) {
            return $this->redirectToRoute(
                'netgen_tags_admin_synonym_add',
                [
                    'mainTagId' => $mainTagId,
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
                'netgen_tags_admin_synonym_add',
                [
                    'mainTagId' => $mainTagId,
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
     * This method renders view with a form for adding new synonym.
     * After form is being submitted, it stores new synonym and redirects to it.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string $mainTagId
     * @param string $languageCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addSynonymAction(Request $request, $mainTagId, $languageCode)
    {
        $this->denyAccessUnlessGranted('ez:tags:addsynonym');

        $synonymCreateStruct = $this->tagsService->newSynonymCreateStruct($mainTagId, $languageCode);

        $form = $this->createForm(
            SynonymCreateType::class,
            $synonymCreateStruct,
            [
                'action' => $request->getPathInfo(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newSynonym = $this->tagsService->addSynonym($form->getData());

            $this->addFlashMessage('success', 'tag_added', ['%tagKeyword%' => $newSynonym->keyword]);

            return $this->redirectToTag($newSynonym);
        }

        return $this->render(
            '@NetgenTags/admin/tag/add.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
