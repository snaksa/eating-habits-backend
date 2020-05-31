<?php

namespace App\Controller;

use App\Builder\MedicineBuilder;
use App\Exception;
use App\Repository\MedicineRepository;
use App\Request\Medicine\CreateMedicineRequest;
use App\Request\Medicine\UpdateMedicineRequest;
use App\Services\AuthorizationService;
use App\Services\FractalManager;
use App\Traits\DateUtils;
use App\Transformers\MedicineTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class MedicinesController extends BaseController
{
    use DateUtils;

    protected string $transformerClass = MedicineTransformer::class;
    private MedicineRepository $medicineRepository;
    private MedicineBuilder $builder;
    private AuthorizationService $authService;

    public function __construct(
        MedicineRepository $medicineRepository,
        MedicineBuilder $builder,
        AuthorizationService $authService,
        FractalManager $fractalManager
    ) {
        $this->medicineRepository = $medicineRepository;
        $this->builder = $builder;
        $this->authService = $authService;

        parent::__construct($fractalManager);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception\NotAuthenticatedException
     */
    public function all(Request $request)
    {
        $this->setRequest($request);

        $medicines = $this->medicineRepository->findUserMedicines($this->authService->getCurrentUser());

        return $this->collection($medicines);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines/{id}", methods={"GET"})
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

        $medicine = $this->medicineRepository->findOneById($id);
        if (!$medicine) {
            $this->notFound("Medicine with ID {$id} was not found");
        }

        if ($medicine->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        return $this->item($medicine);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines", methods={"POST"})
     * @param CreateMedicineRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\InvalidDateException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\InvalidMedicineFrequencyException
     */
    public function create(CreateMedicineRequest $request)
    {
        $this->setRequest($request->getRequest());

        $medicine = $this->builder
            ->create()
            ->bind($request)
            ->setUser($this->authService->getCurrentUser())
            ->build();

        $this->medicineRepository->save($medicine);

        return $this->item($medicine);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines/{id}", methods={"POST"})
     * @param UpdateMedicineRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\NotAuthorizedException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\InvalidMedicineFrequencyException
     */
    public function update(UpdateMedicineRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        $medicine = $this->medicineRepository->findOneById($id);
        if (!$medicine) {
            $this->notFound("Medicine with ID {$id} was not found");
        }

        if ($medicine->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $medicine = $this->builder
            ->setMedicine($medicine)
            ->bind($request)
            ->build();

        $this->medicineRepository->save($medicine);

        return $this->item($medicine);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines/{id}", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\NotAuthorizedException
     * @throws Exception\NotAuthenticatedException
     */
    public function delete(int $id)
    {
        $medicine = $this->medicineRepository->findOneById($id);
        if (!$medicine) {
            throw new Exception\EntityNotFoundException("Medicine with ID {$id} was not found");
        }

        if ($medicine->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $clone = clone $medicine;

        $this->medicineRepository->remove($medicine);

        return $this->item($clone);
    }
}
