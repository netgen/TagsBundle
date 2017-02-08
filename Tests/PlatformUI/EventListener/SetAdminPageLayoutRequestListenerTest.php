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

    public function setUp()
    {
        $this->globalVariable = $this->getMockBuilder(AdminGlobalVariable::class)
            ->disableOriginalConstructor()
            ->setMethods(array('setPageLayoutTemplate'))
            ->getMock();

        $this->pageLayoutTemplate = 'AcmeBundle::pagelayout.html.twig';

        $this->event = $this->getMockBuilder(GetResponseEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(array('isMasterRequest', 'getRequest'))
            ->getMock();

        $this->request = new Request(array(), array(), array('_route' => 'netgen_tags_admin'), array(), array(), array('HTTP_X-PJAX' => 'weeee', 'HTTP_X-Requested-With' => 'XMLHttpRequest'));

        $this->listener = new SetAdminPageLayoutRequestListener($this->globalVariable, $this->pageLayoutTemplate);
    }

    public function testInstanceOfEventSubscriberInterface()
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->listener);
    }

    public function testInstanceOfPlatformUIListener()
    {
        $this->assertInstanceOf(PlatformUIListener::class, $this->listener);
    }

    public function testGetSubscribedEventShouldReturnValidConfiguration()
    {
        $this->assertEquals(array(KernelEvents::REQUEST => 'onKernelRequest'), SetAdminPageLayoutRequestListener::getSubscribedEvents());
    }

    public function testIsMasterRequestFalse()
    {
        $this->event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->event->expects($this->never())
            ->method('getRequest');

        $this->globalVariable->expects($this->never())
            ->method('setPageLayoutTemplate');

        $this->listener->onKernelRequest($this->event);
    }

    public function testIsMasterRequestTrue()
    {
        $this->event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->event->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->globalVariable->expects($this->once())
            ->method('setPageLayoutTemplate')
            ->with($this->pageLayoutTemplate);

        $this->listener->onKernelRequest($this->event);
    }

    public function testIsPlatformUIRequestFalse()
    {
        $this->event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->event->expects($this->once())
            ->method('getRequest')
            ->willReturn(new Request());

        $this->globalVariable->expects($this->never())
            ->method('setPageLayoutTemplate');

        $this->listener->onKernelRequest($this->event);
    }
}
