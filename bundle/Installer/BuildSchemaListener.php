<?php

namespace Netgen\TagsBundle\Installer;

use EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvent;
use EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuildSchemaListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $schemaPath;

    public function __construct(string $schemaPath)
    {
        $this->schemaPath = $schemaPath;
    }

    public static function getSubscribedEvents()
    {
        return [
            SchemaBuilderEvents::BUILD_SCHEMA => 'onBuildSchema',
        ];
    }

    public function onBuildSchema(SchemaBuilderEvent $event)
    {
        $event
            ->getSchemaBuilder()
            ->importSchemaFromFile($this->schemaPath);
    }
}
