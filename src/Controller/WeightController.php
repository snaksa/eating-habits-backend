<?php

namespace App\Controller;

use App\Builder\WeightBuilder;
use App\Exception;
use App\Repository\WeightRepository;
use App\Request\Weight\CreateWeightRequest;
use App\Request\Weight\UpdateWeightRequest;
use App\Services\AuthorizationService;
use App\Services\FractalManager;
use App\Transformers\WeightTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class WeightController extends BaseController
{
    protected string $transformerClass = WeightTransformer::class;
    private WeightRepository $weightRepository;
    private WeightBuilder $builder;
    private AuthorizationService $authService;

    public function __construct(
        WeightRepository $waterSupplyRepository,
        WeightBuilder $builder,
        AuthorizationService $authService,
        FractalManager $fractalManager
    ) {
        $this->weightRepository = $waterSupplyRepository;
        $this->builder = $builder;
        $this->authService = $authService;

        parent::__construct($fractalManager);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/weights", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception\NotAuthenticatedException
     */
    public function all(Request $request)
    {
        $this->setRequest($request);

        $meals = $this->weightRepository->findUserWeights($this->authService->getCurrentUser());

        return $this->collection($meals);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/weights/{id}", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\NotAuthorizedException
     * @throws Exception\NotAuthenticatedException
     */
    public function single(Request $request, int $id)
    {
        $this->setRequest($request);

        $weight = $this->weightRepository->findOneById($id);
        if (!$weight) {
            $this->notFound("Weight with ID {$id} was not found!");
        }

        if ($weight->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }


        return $this->item($weight);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/weights", methods={"POST"})
     * @param CreateWeightRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\InvalidDateException
     * @throws Exception\NotAuthenticatedException
     */
    public function create(CreateWeightRequest $request)
    {
        $this->setRequest($request->getRequest());

        $weight = $this->builder
            ->create()
            ->bind($request)
            ->setUser($this->authService->getCurrentUser())
            ->build();

        $this->weightRepository->save($weight);

        return $this->item($weight);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/weights/{id}", methods={"POST"})
     * @param UpdateWeightRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     * @throws Exception\NotAuthorizedException
     * @throws Exception\NotAuthenticatedException
     */
    public function update(UpdateWeightRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        $weight = $this->weightRepository->findOneById($id);
        if (!$weight) {
            throw new Exception\EntityNotFoundException("Weight with ID {$id} was not found");
        }

        if ($weight->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $weight = $this->builder
            ->setWeight($weight)
            ->bind($request)
            ->build();

        $this->weightRepository->save($weight);

        return $this->item($weight);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/weights/{id}", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\NotAuthorizedException
     */
    public function delete(int $id)
    {
        $waterSupply = $this->weightRepository->findOneById($id);
        if (!$waterSupply) {
            throw new Exception\EntityNotFoundException("Weight with ID {$id} was not found");
        }

        if ($waterSupply->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $clone = clone $waterSupply;

        $this->weightRepository->remove($waterSupply);

        return $this->item($clone);
    }
}
