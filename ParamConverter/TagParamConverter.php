<?php

namespace Netgen\TagsBundle\ParamConverter;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\Exception\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterConfiguration;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class TagParamConverter implements ParamConverterInterface
{
    /**
     * @var \Netgen\TagsBundle\API\Repository\TagsService
     */
    protected $tagsService;

    /**
     * TagParamConverter constructor.
     *
     * @param \Netgen\TagsBundle\API\Repository\TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    /**
     * For given tag ID in the request, it loads a tag and passes it as a parameter to called action mathod.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request The request
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverterConfiguration $configuration)
    {
        $supportedParameters = array(
            'tagId' => 'tag',
            'parentId' => 'parentTag',
        );

        foreach ($supportedParameters as $source => $destination) {
            if (!$request->attributes->has($source)) {
                continue;
            }

            if (empty($request->attributes->get($source))) {
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
                    $request->attributes->get($source)
                )
            );
        }

        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverterConfiguration $configuration)
    {
        return is_a($configuration->getClass(), Tag::class, true);
    }
}
