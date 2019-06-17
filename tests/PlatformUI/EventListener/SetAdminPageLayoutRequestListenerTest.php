<?php

namespace Netgen\TagsBundle\Tests\PlatformUI\EventListener;

use Netgen\TagsBundle\PlatformUI\EventListener\PlatformUIListener;
use Netgen\TagsBundle\PlatformUI\EventListener\SetAdminPageLayoutRequestListener;
use Netgen\TagsBundle\Templating\Twig\AdminGlobalVariable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SetAdminPageLayoutRequestListenerTest extends TestCase
{
    /**
     * @var \Netgen\TagsBundle\PlatformUI\EventListener\SetAdminPageLayoutRequestListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $globalVariable;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $pageLayoutTemplate;

    public function setUp(): void
    {
        $this->globalVariable = $this->getMockBuilder(AdminGlobalVariable::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPageLayoutTemplate'])
            ->getMock();

        $this->pageLayoutTemplate = '@Acme/pagelayout.html.twig';

        $this->event = $this->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMasterRequest', 'getRequest'])
            ->getMock();

        $this->request = new Request(
            [],
            [],
            ['_route' => 'netgen_tags_admin'],
            [],
            [],
            ['HTTP_X-PJAX' => 'weeee', 'HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        $this->listener = new SetAdminPageLayoutRequestListener($this->globalVariable, $this->pageLayoutTemplate);
    }

    public function testInstanceOfEventSubscriberInterface(): void
    {
        self::assertInstanceOf(EventSubscriberInterface::class, $this->listener);
    }

    public function testInstanceOfPlatformUIListener(): void
    {
        self::assertInstanceOf(PlatformUIListener::class, $this->listener);
    }

    public function testGetSubscribedEventShouldReturnValidConfiguration(): void
    {
        self::assertSame([KernelEvents::REQUEST => 'onKernelRequest'], SetAdminPageLayoutRequestListener::getSubscribedEvents());
    }

    public function testIsMasterRequestFalse(): void
    {
        $this->event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->event->expects(self::never())
            ->method('getRequest');

        $this->globalVariable->expects(self::never())
            ->method('setPageLayoutTemplate');

        $this->listener->onKernelRequest($this->event);
    }

    public function testIsMasterRequestTrue(): void
    {
        $this->event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->globalVariable->expects(self::once())
            ->method('setPageLayoutTemplate')
            ->with($this->pageLayoutTemplate);

        $this->listener->onKernelRequest($this->event);
    }

    public function testIsPlatformUIRequestFalse(): void
    {
        $this->event->expects(self::once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->event->expects(self::once())
            ->method('getRequest')
            ->willReturn(new Request());

        $this->globalVariable->expects(self::never())
            ->method('setPageLayoutTemplate');

        $this->listener->onKernelRequest($this->event);
    }
}
