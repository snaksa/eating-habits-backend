<?php

namespace App\Controller;

use App\Builder\MedicineIntakeBuilder;
use App\Exception;
use App\Repository\MedicineIntakeRepository;
use App\Request\MedicineIntake\CreateMedicineIntakeRequest;
use App\Services\AuthorizationService;
use App\Services\FractalManager;
use App\Traits\DateUtils;
use App\Transformers\MedicineIntakeTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class MedicineIntakeController extends BaseController
{
    use DateUtils;

    protected string $transformerClass = MedicineIntakeTransformer::class;
    private MedicineIntakeRepository $medicineIntakeRepository;
    private MedicineIntakeBuilder $builder;
    private AuthorizationService $authService;

    public function __construct(
        MedicineIntakeRepository $medicineIntakeRepository,
        MedicineIntakeBuilder $builder,
        AuthorizationService $authService,
        FractalManager $fractalManager
    ) {
        $this->medicineIntakeRepository = $medicineIntakeRepository;
        $this->builder = $builder;
        $this->authService = $authService;

        parent::__construct($fractalManager);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines-intake", methods={"POST"})
     * @param CreateMedicineIntakeRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\InvalidDateException
     * @throws Exception\InvalidDataException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\NotAuthorizedException
     */
    public function create(CreateMedicineIntakeRequest $request)
    {
        $this->setRequest($request->getRequest());

        $medicineIntake = $this->builder
            ->create()
            ->bind($request)
            ->build();

        $userId = $medicineIntake->getMedicineSchedule()->getMedicine()->getUser()->getId();
        if ($userId !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException(
                'You do not have permissions to set intake for this schedule'
            );
        }

        $this->medicineIntakeRepository->save($medicineIntake);

        return $this->item($medicineIntake);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines-intake/{id}", methods={"DELETE"})
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
        $medicineIntake = $this->medicineIntakeRepository->findOneById($id);
        if (!$medicineIntake) {
            throw new Exception\EntityNotFoundException("MedicineIntake with ID {$id} was not found");
        }

        $userId = $medicineIntake->getMedicineSchedule()->getMedicine()->getUser()->getId();
        if ($userId !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $clone = clone $medicineIntake;

        $this->medicineIntakeRepository->remove($medicineIntake);

        return $this->item($clone);
    }
}
