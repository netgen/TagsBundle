<?php

namespace Netgen\TagsBundle\View\Builder;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\View\TagView;

class TagViewBuilder implements ViewBuilder
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\Configurator
     */
    protected $viewConfigurator;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector
     */
    protected $viewParametersInjector;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    protected $configResolver;

    /**
     * Constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     * @param \eZ\Publish\Core\MVC\Symfony\View\Configurator $viewConfigurator
     * @param \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector $viewParametersInjector
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function __construct(
        TagsService $tagsService,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        ConfigResolverInterface $configResolver
    ) {
        $this->tagsService = $tagsService;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->configResolver = $configResolver;
    }

    /**
     * Tests if the builder matches the given argument.
     *
     * @param mixed $argument Anything the builder can decide against. Example: a controller's request string.
     *
     * @return bool true if the ViewBuilder matches the argument, false otherwise.
     */
    public function matches($argument)
    {
        return strpos($argument, 'eztags.controller.tag_view:') !== false;
    }

    /**
     * Builds the View based on $parameters.
     *
     * @param array $parameters
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If the tag cannot be loaded
     *
     * @return \eZ\Publish\Core\MVC\Symfony\View\View An implementation of the View interface
     */
    public function buildView(array $parameters)
    {
        $view = new TagView();
        if (!empty($parameters['viewType'])) {
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
        if ($view->getViewType() === 'full' && $view->getTemplateIdentifier() === null) {
            $view->setTemplateIdentifier(
                $this->configResolver->getParameter('tag_view.template', 'eztags')
            );
        }

        // Deprecated controller actions are replaced with their new equivalent, viewAction
        if (!$view->getControllerReference() instanceof ControllerReference) {
            if ($parameters['_controller'] === 'eztags.controller.tag_view:viewTag') {
                $view->setControllerReference(
                    new ControllerReference('eztags.controller.tag_view:viewAction')
                );
            }
        }

        $this->viewParametersInjector->injectViewParameters($view, $parameters);

        return $view;
    }
}
