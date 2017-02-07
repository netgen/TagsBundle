<?php

namespace Netgen\TagsBundle\Tests\PlatformUI\EventListener;

use Netgen\TagsBundle\PlatformUI\EventListener\PlatformUIListener;
use Netgen\TagsBundle\PlatformUI\EventListener\RedirectResponseListener;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RedirectResponseListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RedirectResponseListener
     */
    protected $listener;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $kernel;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $platformUiRequest;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    public function setUp()
    {
        $this->listener = new RedirectResponseListener();

        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['isRedirect', 'setStatusCode'])
            ->getMock();

        $this->platformUiRequest = new Request([], [], ['_route' => 'netgen_tags_admin'], [], [], ['HTTP_X-PJAX' => 'weeee', 'HTTP_X-Requested-With' => 'XMLHttpRequest']);

        $this->event = $this->getMockBuilder(FilterResponseEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResponse', 'isMasterRequest', 'getRequest'])
            ->getMock();
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
        $this->assertEquals([KernelEvents::RESPONSE => 'onKernelResponse'], RedirectResponseListener::getSubscribedEvents());
    }

    public function testIfNotMasterRequestThenReturn()
    {
        $this->event->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(false);

        $this->response->expects($this->never())
            ->method('isRedirect');

        $this->event->expects($this->never())
            ->method('getRequest');

        $this->listener->onKernelResponse($this->event);
    }

    public function testIfIsRedirectThenReturn()
    {
        $this->event->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->response->expects($this->once())
            ->method('isRedirect')
            ->willReturn(false);

        $this->event->expects($this->never())
            ->method('getRequest');

        $this->listener->onKernelResponse($this->event);
    }

    public function testValidRequest()
    {
        $url = 'test.com';

        $event = new FilterResponseEvent(
            $this->kernel,
            $this->platformUiRequest,
            HttpKernelInterface::MASTER_REQUEST,
            new RedirectResponse($url)
        );

        $this->listener->onKernelResponse($event);

        $response = $event->getResponse();
        $this->assertEquals(Response::HTTP_RESET_CONTENT, $response->getStatusCode());
        $this->assertEquals($url, $response->headers->get('PJAX-Location'));
        $this->assertFalse($response->headers->has('Location'));
    }

    public function testIsPlatformUIRequestWithRoute()
    {
        $this->event->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->response->expects($this->once())
            ->method('isRedirect')
            ->willReturn(true);

        $this->event->expects($this->once())
            ->method('getRequest')
            ->willReturn(new Request([], [], ['_route' => 'something']));

        $this->response->expects($this->never())
            ->method('setStatusCode');

        $this->listener->onKernelResponse($this->event);
    }

    public function testIsPlatformUIRequestWithHeaders()
    {
        $this->event->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);

        $this->event->expects($this->once())
            ->method('isMasterRequest')
            ->willReturn(true);

        $this->response->expects($this->once())
            ->method('isRedirect')
            ->willReturn(true);

        $this->event->expects($this->once())
            ->method('getRequest')
            ->willReturn(new Request([], [], ['_route' => 'netgen_tags_admin']));

        $this->response->expects($this->never())
            ->method('setStatusCode');

        $this->listener->onKernelResponse($this->event);
    }
}
