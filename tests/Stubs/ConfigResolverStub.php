<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Tests\Stubs;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final class ConfigResolverStub implements ConfigResolverInterface
{
    private string $defaultNamespace = 'ibexa.site_access.config';

    public function __construct(private array $parameters)
    {
    }

    public function getParameter(string $paramName, ?string $namespace = null, ?string $scope = null): mixed
    {
        return $this->parameters[$namespace ?? $this->defaultNamespace][$paramName] ?? null;
    }

    public function hasParameter(string $paramName, ?string $namespace = null, ?string $scope = null): bool
    {
        return isset($this->parameters[$namespace ?? $this->defaultNamespace][$paramName]);
    }

    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }
}
