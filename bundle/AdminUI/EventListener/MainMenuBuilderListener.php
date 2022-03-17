<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\AdminUI\EventListener;

use Ibexa\AdminUi\Menu\Event\ConfigureMenuEvent;
use Ibexa\AdminUi\Menu\MainMenuBuilder;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use function array_keys;
use function array_search;
use function array_splice;
use function is_int;

final class MainMenuBuilderListener implements EventSubscriberInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [ConfigureMenuEvent::MAIN_MENU => 'onMainMenuBuild'];
    }

    /**
     * This method adds Netgen Tags menu items to Ibexa Platform admin interface.
     */
    public function onMainMenuBuild(ConfigureMenuEvent $event): void
    {
        if (!$this->authorizationChecker->isGranted('ibexa:tags:read')) {
            return;
        }

        $this->addTagsSubMenu($event->getMenu());
    }

    /**
     * Adds the Netgen Tags submenu to Ibexa Platform admin interface.
     */
    private function addTagsSubMenu(ItemInterface $menu): void
    {
        $menuOrder = $this->getNewMenuOrder($menu);

        $menu
            ->addChild('netgen_tags', ['route' => 'netgen_tags_admin_root'])
            ->setLabel('menu.main_menu.header')
            ->setExtra('translation_domain', 'netgen_tags_admin')
            ->setExtra('bottom_item', true)
            ->setExtra('icon', 'tags')
            ->setExtra('orderNumber', 150)
            ->setAttribute('data-tooltip-placement', 'right')
            ->setAttribute('data-tooltip-extra-class', 'ibexa-tooltip--info-neon');

        $menu->reorderChildren($menuOrder);
    }

    /**
     * Returns the new menu order.
     */
    private function getNewMenuOrder(ItemInterface $menu): array
    {
        $menuOrder = array_keys($menu->getChildren());
        $configMenuIndex = array_search(MainMenuBuilder::ITEM_ADMIN, $menuOrder, true);
        if (is_int($configMenuIndex)) {
            array_splice($menuOrder, $configMenuIndex, 0, ['netgen_tags']);

            return $menuOrder;
        }

        $menuOrder[] = 'netgen_tags';

        return $menuOrder;
    }
}
