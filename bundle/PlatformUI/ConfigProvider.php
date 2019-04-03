<?php

namespace Netgen\TagsBundle\PlatformUI;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\PlatformUIBundle\ApplicationConfig\Provider;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ConfigProvider implements Provider
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        UrlGeneratorInterface $urlGenerator,
        ConfigResolverInterface $configResolver
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator = $urlGenerator;
        $this->configResolver = $configResolver;
    }

    /**
     * @return mixed Anything that is serializable via json_encode()
     */
    public function getConfig()
    {
        return [
            'field' => [
                'hasAddAccess' => $this->authorizationChecker->isGranted('ez:tags:add'),
                'autoCompleteLimit' => $this->configResolver->getParameter('field.autocomplete_limit', 'eztags'),
                'urls' => [
                    'autoComplete' => $this->urlGenerator->generate('netgen_tags_admin_field_autocomplete'),
                    'addTagButtonVisibility' => $this->urlGenerator->generate('netgen_tags_admin_access_add_tags'),
                    'treeChildren' => $this->urlGenerator->generate(
                        'netgen_tags_admin_tree_get_children',
                        [
                            'tagId' => '_tagId_',
                        ]
                    ),
                ],
            ],
        ];
    }
}
