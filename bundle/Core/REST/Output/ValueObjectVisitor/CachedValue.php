<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\REST\Output\ValueObjectVisitor;

use FOS\HttpCache\ResponseTagger;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Rest\Output\Generator;
use Ibexa\Contracts\Rest\Output\ValueObjectVisitor;
use Ibexa\Contracts\Rest\Output\Visitor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class CachedValue extends ValueObjectVisitor
{
    private RequestStack $requestStack;

    private ConfigResolverInterface $configResolver;

    private ResponseTagger $responseTagger;

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

        if ($this->getParameter('tag_view.cache', 'netgen_tags') !== true) {
            return;
        }

        $response = $visitor->getResponse();
        $response->setPublic();
        $response->setVary('Accept');

        if ($this->getParameter('tag_view.ttl_cache', 'netgen_tags') === true) {
            $response->setSharedMaxAge($this->getParameter('tag_view.default_ttl', 'netgen_tags'));

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
