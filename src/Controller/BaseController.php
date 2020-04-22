<?php

namespace App\Controller;

use App\Exception\EntityNotFoundException;
use App\Services\FractalManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BaseController
{
    protected FractalManager $fractalManager;
    protected string $transformerClass;

    /**
     * BaseController constructor.
     * @param FractalManager $fractal
     */
    public function __construct(FractalManager $fractal)
    {
        $this->fractalManager = $fractal;
    }

    public function setRequest(Request $request)
    {
        $this->fractalManager->parseIncludes($request->get('include', ''));
        $this->fractalManager->parseExcludes($request->get('exclude', ''));
    }

    /**
     * @param $data
     * @param string $transformerClass
     * @param null $resourceKey
     * @return JsonResponse
     */
    public function item($data, string $transformerClass = null, $resourceKey = null)
    {
        if (!$transformerClass) {
            $transformerClass = $this->transformerClass;
        }

        $transformer = new $transformerClass();
        $item = $this->fractalManager->item($data, $transformer, $resourceKey);

        return new JsonResponse($item);
    }

    /**
     * @param $data
     * @param string $transformerClass
     * @param null $resourceKey
     * @return JsonResponse
     */
    public function collection($data, string $transformerClass = null, $resourceKey = null)
    {
        if (!$transformerClass) {
            $transformerClass = $this->transformerClass;
        }

        $transformer = new $transformerClass();
        $collection = $this->fractalManager->collection($data, $transformer, $resourceKey);

        return new JsonResponse($collection);
    }

    /**
     * @param string $message
     * @throws EntityNotFoundException
     */
    public function notFound(string $message) {
        throw new EntityNotFoundException($message, JsonResponse::HTTP_NOT_FOUND);
    }
}
