<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\ParamConverter;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Exception\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterConfiguration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

use function in_array;
use function is_a;
use function sprintf;

final class TagParamConverter implements ParamConverterInterface
{
    private TagsService $tagsService;

    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * For given tag ID in the request, it loads a tag and passes it as a parameter to called action method.
     *
     * @throws \Netgen\TagsBundle\Exception\InvalidArgumentException If the required argument is empty
     */
    public function apply(Request $request, ParamConverterConfiguration $configuration): bool
    {
        $supportedParameters = [
            'tagId' => 'tag',
            'parentId' => 'parentTag',
        ];

        foreach ($supportedParameters as $source => $destination) {
            if (!$request->attributes->has($source)) {
                continue;
            }

            if (in_array($request->attributes->get($source), ['0', 0, 0.0, '', null, false], true)) {
                if ($configuration->isOptional()) {
                    continue;
                }

                throw new InvalidArgumentException(
                    sprintf('Required request attribute "%s" is empty.', $source)
                );
            }

            $request->attributes->set(
                $destination,
                $this->tagsService->loadTag(
                    (int) $request->attributes->get($source)
                )
            );
        }

        return true;
    }

    public function supports(ParamConverterConfiguration $configuration): bool
    {
        return is_a($configuration->getClass(), Tag::class, true);
    }
}
