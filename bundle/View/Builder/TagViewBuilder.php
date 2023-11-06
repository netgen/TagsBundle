<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\View\Builder;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\MVC\Symfony\View\Builder\ViewBuilder;
use Ibexa\Core\MVC\Symfony\View\Configurator;
use Ibexa\Core\MVC\Symfony\View\ParametersInjector;
use Ibexa\Core\MVC\Symfony\View\View;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\View\TagView;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use function is_string;
use function str_contains;

final class TagViewBuilder implements ViewBuilder
{
    public function __construct(
        private TagsService $tagsService,
        private Configurator $viewConfigurator,
        private ParametersInjector $viewParametersInjector,
        private ConfigResolverInterface $configResolver,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {}

    public function matches(mixed $argument): bool
    {
        return is_string($argument) && str_contains($argument, 'netgen_tags.controller.tag_view:');
    }

    public function buildView(array $parameters): View
    {
        if (!$this->authorizationChecker->isGranted('ibexa:tags:view')) {
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
                    $this->configResolver->getParameter('tag_view.template', 'netgen_tags'),
                );
            }
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        return $view;
    }
}
