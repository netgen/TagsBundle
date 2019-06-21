<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Installer;

use EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvent;
use EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BuildSchemaListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $schemaPath;

    public function __construct(string $schemaPath)
    {
        $this->schemaPath = $schemaPath;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SchemaBuilderEvents::BUILD_SCHEMA => 'onBuildSchema',
        ];
    }

    public function onBuildSchema(SchemaBuilderEvent $event): void
    {
        $event
            ->getSchemaBuilder()
            ->importSchemaFromFile($this->schemaPath);
    }
}
