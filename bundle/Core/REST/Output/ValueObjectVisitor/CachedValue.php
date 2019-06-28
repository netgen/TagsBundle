<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use EzSystems\EzPlatformRest\Output\Generator;
use EzSystems\EzPlatformRest\Output\ValueObjectVisitor;
use EzSystems\EzPlatformRest\Output\Visitor;
use FOS\HttpCache\ResponseTagger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class CachedValue extends ValueObjectVisitor
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \FOS\HttpCache\ResponseTagger
     */
    private $responseTagger;

    public function __construct(
        RequestStack $requestStack,
        ConfigResolverInterface $configResolver,
        ResponseTagger $responseTagger
    ) {
        $this->requestStack = $requestStack;
        $this->configResolver = $configResolver;
        $this->responseTagger = $responseTagger;
    }

    public function visit(Visitor $visitor, Generator $generator, $data): void
    {
        $visitor->visitValueObject($data->value);

        if ($this->getParameter('tag_view.cache', 'eztags') !== true) {
            return;
        }

        $response = $visitor->getResponse();
        $response->setPublic();
        $response->setVary('Accept');

        if ($this->getParameter('tag_view.ttl_cache', 'eztags') === true) {
            $response->setSharedMaxAge($this->getParameter('tag_view.default_ttl', 'eztags'));

            $request = $this->requestStack->getCurrentRequest();
            if ($request instanceof Request && $request->headers->has('X-User-Hash')) {
                $response->setVary('X-User-Hash', false);
            }
        }

        if (isset($data->cacheTags['tagId'])) {
            $this->responseTagger->addTags(['ngtags-tag-' . $data->cacheTags['tagId']]);
        }

        if (isset($data->cacheTags['tagKeyword'])) {
            $this->responseTagger->addTags(['ngtags-tag-keyword-' . $data->cacheTags['tagKeyword']]);
        }
    }

    /**
     * Returns the parameter value from config resolver.
     *
     * @param string $parameterName
     * @param string $namespace
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    private function getParameter(string $parameterName, string $namespace, $defaultValue = null)
    {
        if ($this->configResolver->hasParameter($parameterName, $namespace)) {
            return $this->configResolver->getParameter($parameterName, $namespace);
        }

        return $defaultValue;
    }
}
