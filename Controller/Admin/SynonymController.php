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
        $form = $this->createForm(
            LanguageSelectType::class,
            null,
            array(
                'action' => $request->getPathInfo(),
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute(
                'netgen_tags_admin_synonym_add',
                array(
                    'mainTagId' => $mainTagId,
                    'languageCode' => $form->getData()['languageCode'],
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/synonym:select_translation.html.twig',
            array(
                'form' => $form->createView(),
            )
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
        $synonymCreateStruct = $this->tagsService->newSynonymCreateStruct($mainTagId, $languageCode);

        $form = $this->createForm(
            SynonymCreateType::class,
            $synonymCreateStruct,
            array(
                'action' => $request->getPathInfo(),
                'languageCode' => $languageCode,
            )
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newSynonym = $this->tagsService->addSynonym($form->getData());

            $this->addFlash(
                'successMessages',
                $this->translator->trans(
                    'synonym.add.success',
                    array(
                        '%tagKeyword%' => $newSynonym->keyword,
                    ),
                    'eztags_admin'
                )
            );

            return $this->redirectToTagOrDashboard($newSynonym);
        }

        return $this->render(
            'NetgenTagsBundle:admin/synonym:add.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }
}
