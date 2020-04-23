<?php

namespace App\Controller;

use App\Builder\WeightBuilder;
use App\Exception;
use App\Repository\WeightRepository;
use App\Request\Weight\CreateWeightRequest;
use App\Request\Weight\UpdateWeightRequest;
use App\Services\FractalManager;
use App\Transformers\WeightTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WeightController extends BaseController
{
    protected string $transformerClass = WeightTransformer::class;
    private WeightRepository $weightRepository;
    private WeightBuilder $builder;

    public function __construct(
        WeightRepository $waterSupplyRepository,
        WeightBuilder $builder,
        FractalManager $fractalManager
    ) {
        $this->weightRepository = $waterSupplyRepository;
        $this->builder = $builder;

        parent::__construct($fractalManager);
    }

    /**
     * @Route("/weights", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $this->setRequest($request);

        $meals = $this->weightRepository->findAll();

        return $this->collection($meals);
    }

    /**
     * @Route("/weights/{id}", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws Exception\EntityNotFoundException
     */
    public function single(Request $request, int $id)
    {
        $this->setRequest($request);

        $waterSupply = $this->weightRepository->findOneById($id);
        if (!$waterSupply) {
            $this->notFound("Weight with ID {$id} was not found!");
        }

        return $this->item($waterSupply);
    }

    /**
     * @Route("/weights", methods={"POST"})
     * @param CreateWeightRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     */
    public function create(CreateWeightRequest $request)
    {
        $this->setRequest($request->getRequest());

        $waterSupply = $this->builder
            ->create()
            ->bind($request)
            ->build();

        $this->weightRepository->save($waterSupply);

        return $this->item($waterSupply);
    }

    /**
     * @Route("/weights/{id}", methods={"POST"})
     * @param UpdateWeightRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     */
    public function update(UpdateWeightRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        $waterSupply = $this->weightRepository->findOneById($id);
        if (!$waterSupply) {
            throw new Exception\EntityNotFoundException("Weight with ID {$id} was not found");
        }

        $waterSupply = $this->builder
            ->setWeight($waterSupply)
            ->bind($request)
            ->build();

        $this->weightRepository->save($waterSupply);

        return $this->item($waterSupply);
    }

    /**
     * @Route("/weights/{id}", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function delete(int $id)
    {
        $waterSupply = $this->weightRepository->findOneById($id);
        if (!$waterSupply) {
            throw new Exception\EntityNotFoundException("Weight with ID {$id} was not found");
        }

        $clone = clone $waterSupply;

        $this->weightRepository->remove($waterSupply);

        return $this->item($clone);
    }
}
