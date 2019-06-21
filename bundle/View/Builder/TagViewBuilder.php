<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\Builder;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use eZ\Publish\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\View\TagView;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class TagViewBuilder implements ViewBuilder
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    private $tagsService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Configurator
     */
    private $viewConfigurator;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector
     */
    private $viewParametersInjector;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        TagsService $tagsService,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        ConfigResolverInterface $configResolver,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->tagsService = $tagsService;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->configResolver = $configResolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function matches($argument): bool
    {
        return is_string($argument) && mb_strpos($argument, 'eztags.controller.tag_view:') !== false;
    }

    public function buildView(array $parameters): View
    {
        if (!$this->authorizationChecker->isGranted('ez:tags:view')) {
            throw new AccessDeniedException();
        }

        $view = new TagView();
        if (is_string($parameters['viewType']) && $parameters['viewType'] !== '') {
            $view->setViewType($parameters['viewType']);
        }

        if (isset($parameters['tagId'])) {
            $view->setTag($this->tagsService->loadTag($parameters['tagId']));
        } elseif (isset($parameters['tag']) && $parameters['tag'] instanceof Tag) {
            $view->setTag($parameters['tag']);
        } else {
            throw new InvalidArgumentException('Tag', 'No tag could be loaded from parameters');
        }

        $this->viewConfigurator->configure($view);

        // We want to have a default template for full tag view
        if (!$view->getControllerReference() instanceof ControllerReference) {
            if ($view->getViewType() === 'full' && !is_string($view->getTemplateIdentifier())) {
                $view->setTemplateIdentifier(
                    $this->configResolver->getParameter('tag_view.template', 'eztags')
                );
            }
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        return $view;
    }
}
