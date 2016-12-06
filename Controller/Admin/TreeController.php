<?php

namespace Netgen\TagsBundle\Controller\Admin;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class TreeController extends Controller
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
     * TreeController constructor.
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
     * Returns JSON string containing all children tags for given tag.
     * It is called in AJAX request from jsTree Javascript plugin to render tree with tags.
     * It supports lazy loading; when a tag is clicked in a tree, it calls this method to fetch it's children.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag|null $tag
     * @param bool $isRoot
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getChildrenAction(Tag $tag = null, $isRoot = false)
    {
        $childrenTags = $this->tagsService->loadTagChildren($tag);

        $result = array();

        if ((bool) $isRoot) {
            if ($tag === null) {
                $result = array(
                    array(
                        'id' => '0',
                        'parent' => '#',
                        'text' => $this->translator->trans(
                            'tag.tree.top_level',
                            array(),
                            'eztags_admin'
                        ),
                        'children' => true,
                        'state' => array(
                            'opened' => true,
                        ),
                        'a_attr' => array(
                            'href' => $this->generateUrl(
                                'netgen_tags_admin_dashboard_index'
                            ),
                        ),
                        'data' => array(
                            'add_child' => array(
                                'url' => $this->generateUrl(
                                    'netgen_tags_admin_tag_add_select',
                                    array(
                                        'parentId' => 0,
                                    )
                                ),
                                'text' => $this->translator->trans(
                                    'tag.tree.add_child',
                                    array(),
                                    'eztags_admin'
                                ),
                            ),
                        ),
                    ),
                );
            } else {
                $result = $this->getTagTreeData($tag, $isRoot);
            }
        } else {
            foreach ($childrenTags as $tag) {
                $result[] = $this->getTagTreeData($tag, $isRoot);
            }
        }

        return (new JsonResponse())->setData($result);
    }

    /**
     * Generates data, for given tag, which will be converted to JSON:.
     *
     * @param \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag
     * @param bool $isRoot
     *
     * @return array
     */
    protected function getTagTreeData(Tag $tag, $isRoot = false)
    {
        $synonymCount = $tag === null ?
            0 :
            $this->tagsService->getTagSynonymCount($tag);

        return array(
            'id' => $tag->id,
            'parent' => $isRoot ? '#' : $tag->parentTagId,
            'text' => $synonymCount > 0 ? $tag->keyword . ' (+' . $synonymCount . ')' : $tag->keyword,
            'children' => $this->tagsService->getTagChildrenCount($tag) > 0,
            'a_attr' => array(
                'href' => $this->generateUrl(
                    'netgen_tags_admin_tag_show',
                    array(
                        'tagId' => $tag->id,
                    )
                ),
            ),
            'data' => array(
                'add_child' => array(
                    'url' => $this->generateUrl(
                        'netgen_tags_admin_tag_add_select',
                        array(
                            'parentId' => $tag->id,
                        )
                    ),
                    'text' => $this->translator->trans(
                        'tag.tree.add_child',
                        array(),
                        'eztags_admin'
                    ),
                ),
                'edit_tag' => array(
                    'url' => $this->generateUrl(
                        'netgen_tags_admin_tag_update_select',
                        array(
                            'parentId' => $tag->id,
                        )
                    ),
                    'text' => $this->translator->trans(
                        'tag.tree.edit_tag',
                        array(),
                        'eztags_admin'
                    ),
                ),
            ),
        );
    }
}
