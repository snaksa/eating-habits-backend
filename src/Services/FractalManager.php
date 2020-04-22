<?php

namespace App\Services;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\TransformerAbstract;

class FractalManager
{
    private Manager $manager;

    public function __construct(Manager $manager, DataArraySerializer $serializer)
    {
        $this->manager = $manager;
        $this->manager->setSerializer($serializer);
    }

    public function item($data, TransformerAbstract $transformer, $resourceKey = null)
    {
        return $this->createDataArray(
            new Item($data, $transformer, $resourceKey)
        );
    }

    public function collection($data, TransformerAbstract $transformer, $resourceKey = null)
    {
        return $this->createDataArray(
            new Collection($data, $transformer, $resourceKey)
        );
    }

    /**
     * @param ResourceInterface $resource
     * @return array|null
     */
    public function createDataArray(ResourceInterface $resource)
    {
        return $this->manager->createData($resource)->toArray();
    }

    /**
     * Get the includes from the request if none are passed.
     *
     * @param null $includes
     */
    public function parseIncludes($includes = null)
    {
        $this->manager->parseIncludes($includes);
    }

    /**
     * Get the excludes from the request if none are passed.
     *
     * @param null $excludes
     */
    public function parseExcludes($excludes = null)
    {
        $this->manager->parseExcludes($excludes);
    }
}
