<?php

namespace Netgen\TagsBundle\ParamConverter;

use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Exception\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterConfiguration;
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
        if (!$request->attributes->has('tagId')) {
            return false;
        }

        if (empty($request->attributes->get('tagId'))) {
            if ($configuration->isOptional()) {
                return false;
            }

            throw new InvalidArgumentException(
                'Required request attribute "tagId" is empty.'
            );
        }

        $request->attributes->set(
            'tag',
            $this->tagsService->loadTag($request->attributes->get('tagId'))
        );

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
        return is_a($configuration->getClass(), 'Netgen\TagsBundle\API\Repository\Values\Tags\Tag', true);
    }
}
