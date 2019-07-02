<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Stubs;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherStub implements EventDispatcherInterface
{
    public function dispatch($event): object
    {
        return $event;
    }
}
