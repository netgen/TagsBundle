<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class SynonymController extends Controller
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var array
     */
    protected $languages;

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
     * @param array|null $languages
     */
    public function setLanguages(array $languages = null)
    {
        $this->languages = $languages;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|string $mainTagId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addSynonymSelectAction(Request $request, $mainTagId)
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
            'Netgen\TagsBundle\Form\Type\SynonymCreateType',
            $synonymCreateStruct,
            array(
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

            return $this->redirectToRoute(
                'netgen_tags_admin_tag_show',
                array(
                    'tagId' => $newSynonym->id,
                )
            );
        }

        return $this->render(
            'NetgenTagsBundle:admin/synonym:add.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }
}
