<?php

namespace Netgen\TagsBundle\PlatformAdminUI\EventListener;

use EzSystems\EzPlatformAdminUi\Menu\Event\ConfigureMenuEvent;
use EzSystems\EzPlatformAdminUi\Menu\MainMenuBuilder;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MainMenuBuilderListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents()
    {
        return [ConfigureMenuEvent::MAIN_MENU => 'onMainMenuBuild'];
    }

    /**
     * This method adds Netgen Tags menu items to eZ Platform admin interface.
     *
     * @param \EzSystems\EzPlatformAdminUi\Menu\Event\ConfigureMenuEvent $event
     */
    public function onMainMenuBuild(ConfigureMenuEvent $event)
    {
        if (!$this->authorizationChecker->isGranted('ez:tags:read')) {
            return;
        }

        $this->addTagsSubMenu($event->getMenu());
    }

    /**
     * Adds the Netgen Tags submenu to eZ Platform admin interface.
     *
     * @param \Knp\Menu\ItemInterface $menu
     */
    private function addTagsSubMenu(ItemInterface $menu)
    {
        $menuOrder = $this->getNewMenuOrder($menu);

        $menu
            ->addChild('eztags', ['route' => 'netgen_tags_admin_root'])
            ->setLabel('menu.main_menu.header')
            ->setExtra('translation_domain', 'eztags_admin');

        $menu->reorderChildren($menuOrder);
    }

    /**
     * Returns the new menu order.
     *
     * @param \Knp\Menu\ItemInterface $menu
     *
     * @return array
     */
    private function getNewMenuOrder(ItemInterface $menu)
    {
        $menuOrder = array_keys($menu->getChildren());
        $configMenuIndex = array_search(MainMenuBuilder::ITEM_ADMIN, $menuOrder, true);
        if ($configMenuIndex !== false) {
            array_splice($menuOrder, array_search(MainMenuBuilder::ITEM_ADMIN, $menuOrder, true), 0, ['eztags']);

            return $menuOrder;
        }

        $menuOrder[] = 'eztags';

        return $menuOrder;
    }
}
