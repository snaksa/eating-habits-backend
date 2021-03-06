<?php

namespace App\Controller;

use App\Builder\WaterSupplyBuilder;
use App\Exception;
use App\Repository\WaterSupplyRepository;
use App\Request\WaterSupply\CreateWaterSupplyRequest;
use App\Request\WaterSupply\UpdateWaterSupplyRequest;
use App\Services\AuthorizationService;
use App\Services\FractalManager;
use App\Traits\DateUtils;
use App\Transformers\WaterSupplyTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class WaterSupplyController extends BaseController
{
    use DateUtils;

    protected string $transformerClass = WaterSupplyTransformer::class;
    private WaterSupplyRepository $waterSupplyRepository;
    private WaterSupplyBuilder $builder;
    private AuthorizationService $authService;

    public function __construct(
        WaterSupplyRepository $waterSupplyRepository,
        WaterSupplyBuilder $builder,
        AuthorizationService $authService,
        FractalManager $fractalManager
    ) {
        $this->waterSupplyRepository = $waterSupplyRepository;
        $this->builder = $builder;
        $this->authService = $authService;

        parent::__construct($fractalManager);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/water-supplies", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception\NotAuthenticatedException
     */
    public function all(Request $request)
    {
        $this->setRequest($request);

        $waterSupplies = $this->waterSupplyRepository->findUserWaterSupplies($this->authService->getCurrentUser());

        return $this->collection($waterSupplies);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/water-supplies/byDay", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception\NotAuthenticatedException
     * @throws \Exception
     */
    public function byDay(Request $request)
    {
        $this->setRequest($request);

        $date = $request->get('date', null);

        if ($date) {
            $startDate = $this->createFromFormat($date);
            $endDate = (clone $startDate)->modify('+ 24 hours');
        } else {
            $startDate = $this->getCurrentDateTime()->setTime(0, 0, 0);
            $endDate = (clone $startDate)->modify('+ 24 hours');
        }

        $waterSupplies = $this->waterSupplyRepository
            ->findUserWaterSuppliesByDay($this->authService->getCurrentUser(), $startDate, $endDate);

        return $this->collection($waterSupplies);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/water-supplies/groupByDays", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception\NotAuthenticatedException
     * @throws \Exception
     */
    public function groupByDays(Request $request)
    {
        $this->setRequest($request);

        $startDate = $request->get('startDate', null);
        if (!$startDate) {
            $startDate = $this->getCurrentDateTime()->setTime(0, 0, 0)->modify('- 6 days');
        } else {
            $startDate = $this->createFromFormat($startDate);
        }

        $endDate = $request->get('endDate', null);
        if (!$endDate) {
            $time = $startDate->format('H:i:s');
            $time = explode(':', $time);
            $endDate = $this->getCurrentDateTime()->setTime($time[0], $time[1], $time[2]);
        } else {
            $endDate = $this->createFromFormat($endDate);
        }

        $waterSupplies = $this->waterSupplyRepository
            ->findUserWaterSuppliesGroupByDay($this->authService->getCurrentUser(), $startDate, $endDate);

        $currentDay = (clone $startDate)->modify('+ 1 days');
        $total = 0;
        $result = [];

        $i = 0;
        while ($i < count($waterSupplies)) {
            while ($waterSupplies[$i]->getDate() < $currentDay) {
                $total += $waterSupplies[$i]->getAmount();
                $i++;

                if ($i === count($waterSupplies)) {
                    break;
                }
            }

            $result[] = [
                'date' => $this->formatDate($startDate),
                'amount' => $total
            ];

            $total = 0;
            $startDate = $startDate->modify('+ 1 days');
            $currentDay = (clone $startDate)->modify('+ 1 days');
        }

        while ($startDate < $endDate) {
            $result[] = [
                'date' => $this->formatDate($startDate),
                'amount' => $total
            ];

            $startDate->modify('+ 1 days');
        }

        return new JsonResponse([
            'data' => $result
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/water-supplies/{id}", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\NotAuthorizedException
     */
    public function single(Request $request, int $id)
    {
        $this->setRequest($request);

        $waterSupply = $this->waterSupplyRepository->findOneById($id);
        if (!$waterSupply) {
            $this->notFound("WaterSupply with ID {$id} was not found");
        }

        if ($waterSupply->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        return $this->item($waterSupply);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/water-supplies", methods={"POST"})
     * @param CreateWaterSupplyRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     * @throws Exception\NotAuthenticatedException
     */
    public function create(CreateWaterSupplyRequest $request)
    {
        $this->setRequest($request->getRequest());

        $waterSupply = $this->builder
            ->create()
            ->bind($request)
            ->setUser($this->authService->getCurrentUser())
            ->build();

        $this->waterSupplyRepository->save($waterSupply);

        return $this->item($waterSupply);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/water-supplies/{id}", methods={"POST"})
     * @param UpdateWaterSupplyRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\NotAuthorizedException
     */
    public function update(UpdateWaterSupplyRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        $waterSupply = $this->waterSupplyRepository->findOneById($id);
        if (!$waterSupply) {
            $this->notFound("WaterSupply with ID {$id} was not found");
        }

        if ($waterSupply->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $waterSupply = $this->builder
            ->setWaterSupply($waterSupply)
            ->bind($request)
            ->build();

        $this->waterSupplyRepository->save($waterSupply);

        return $this->item($waterSupply);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/water-supplies/{id}", methods={"DELETE"})
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
        $waterSupply = $this->waterSupplyRepository->findOneById($id);
        if (!$waterSupply) {
            throw new Exception\EntityNotFoundException("WaterSupply with ID {$id} was not found");
        }

        if ($waterSupply->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $clone = clone $waterSupply;

        $this->waterSupplyRepository->remove($waterSupply);

        return $this->item($clone);
    }
}
