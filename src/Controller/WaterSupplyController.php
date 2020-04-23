<?php

namespace App\Controller;

use App\Builder\WaterSupplyBuilder;
use App\Exception;
use App\Repository\WaterSupplyRepository;
use App\Request\WaterSupply\CreateWaterSupplyRequest;
use App\Request\WaterSupply\UpdateWaterSupplyRequest;
use App\Services\FractalManager;
use App\Transformers\WaterSupplyTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WaterSupplyController extends BaseController
{
    protected string $transformerClass = WaterSupplyTransformer::class;
    private WaterSupplyRepository $waterSupplyRepository;
    private WaterSupplyBuilder $builder;

    public function __construct(
        WaterSupplyRepository $waterSupplyRepository,
        WaterSupplyBuilder $builder,
        FractalManager $fractalManager
    ) {
        $this->waterSupplyRepository = $waterSupplyRepository;
        $this->builder = $builder;

        parent::__construct($fractalManager);
    }

    /**
     * @Route("/water-supplies", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $this->setRequest($request);

        $meals = $this->waterSupplyRepository->findAll();

        return $this->collection($meals);
    }

    /**
     * @Route("/water-supplies/{id}", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws Exception\EntityNotFoundException
     */
    public function single(Request $request, int $id)
    {
        $this->setRequest($request);

        $waterSupply = $this->waterSupplyRepository->findOneById($id);
        if (!$waterSupply) {
            $this->notFound("WaterSupply with ID {$id} was not found!");
        }

        return $this->item($waterSupply);
    }

    /**
     * @Route("/water-supplies", methods={"POST"})
     * @param CreateWaterSupplyRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     */
    public function create(CreateWaterSupplyRequest $request)
    {
        $this->setRequest($request->getRequest());

        $waterSupply = $this->builder
            ->create()
            ->bind($request)
            ->build();

        $this->waterSupplyRepository->save($waterSupply);

        return $this->item($waterSupply);
    }

    /**
     * @Route("/water-supplies/{id}", methods={"POST"})
     * @param UpdateWaterSupplyRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     */
    public function update(UpdateWaterSupplyRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        $waterSupply = $this->waterSupplyRepository->findOneById($id);
        if (!$waterSupply) {
            throw new Exception\EntityNotFoundException("WaterSupply with ID {$id} was not found");
        }

        $waterSupply = $this->builder
            ->setWaterSupply($waterSupply)
            ->bind($request)
            ->build();

        $this->waterSupplyRepository->save($waterSupply);

        return $this->item($waterSupply);
    }

    /**
     * @Route("/water-supplies/{id}", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function delete(int $id)
    {
        $waterSupply = $this->waterSupplyRepository->findOneById($id);
        if (!$waterSupply) {
            throw new Exception\EntityNotFoundException("WaterSupply with ID {$id} was not found");
        }

        $clone = clone $waterSupply;

        $this->waterSupplyRepository->remove($waterSupply);

        return $this->item($clone);
    }
}
