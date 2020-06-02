<?php

namespace App\Controller;

use App\Builder\MedicineScheduleBuilder;
use App\Entity\MedicineSchedule;
use App\Exception;
use App\Repository\MedicineScheduleRepository;
use App\Request\MedicineSchedule\CreateMedicineScheduleRequest;
use App\Request\MedicineSchedule\UpdateMedicineScheduleRequest;
use App\Services\AuthorizationService;
use App\Services\FractalManager;
use App\Traits\DateUtils;
use App\Transformers\MedicineScheduleTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class MedicineScheduleController extends BaseController
{
    use DateUtils;

    protected string $transformerClass = MedicineScheduleTransformer::class;
    private MedicineScheduleRepository $medicineScheduleRepository;
    private MedicineScheduleBuilder $builder;
    private AuthorizationService $authService;

    public function __construct(
        MedicineScheduleRepository $medicineRepository,
        MedicineScheduleBuilder $builder,
        AuthorizationService $authService,
        FractalManager $fractalManager
    ) {
        $this->medicineScheduleRepository = $medicineRepository;
        $this->builder = $builder;
        $this->authService = $authService;

        parent::__construct($fractalManager);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines-schedule/byDay", methods={"GET"})
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

        $currentUser = $this->authService->getCurrentUser();

        $scheduledMedicines = $this->medicineScheduleRepository
            ->findUserEverydayAndOnceScheduledMedicinesByDay(
                $currentUser,
                $startDate,
                $endDate
            );

        $periodMedicines = $this->medicineScheduleRepository->findUserPeriodMedicines($currentUser);

        foreach ($periodMedicines as $period) {
            $medicineStartDate = $period->getIntakeTime();
            while ($medicineStartDate < $endDate) {
                if ($startDate <= $medicineStartDate) {
                    $period->setIntakeTime($medicineStartDate);
                    $scheduledMedicines[] = $period;
                    break;
                }

                $medicineStartDate->add(new \DateInterval("PT{$period->getPeriodSpan()}S"));
            }
        }

        usort($scheduledMedicines, function (MedicineSchedule $a, MedicineSchedule $b) {
            return $a->getIntakeTime()->format('H:i:s') > $b->getIntakeTime()->format('H:i:s');
        });

        return $this->collection($scheduledMedicines);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines-schedule", methods={"POST"})
     * @param CreateMedicineScheduleRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\InvalidDateException
     * @throws Exception\InvalidDataException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\NotAuthorizedException
     */
    public function create(CreateMedicineScheduleRequest $request)
    {
        $this->setRequest($request->getRequest());

        $medicineSchedule = $this->builder
            ->create()
            ->bind($request)
            ->build();

        if ($medicineSchedule->getMedicine()->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to set schedule for this medicine');
        }

        $this->medicineScheduleRepository->save($medicineSchedule);

        return $this->item($medicineSchedule);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines-schedule/{id}", methods={"POST"})
     * @param UpdateMedicineScheduleRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\NotAuthorizedException
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\InvalidDataException
     */
    public function update(UpdateMedicineScheduleRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        $medicineSchedule = $this->medicineScheduleRepository->findOneById($id);
        if (!$medicineSchedule) {
            $this->notFound("MedicineSchedule with ID {$id} was not found");
        }

        if ($medicineSchedule->getMedicine()->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $medicineSchedule = $this->builder
            ->setMedicineSchedule($medicineSchedule)
            ->bind($request)
            ->build();

        $this->medicineScheduleRepository->save($medicineSchedule);

        return $this->item($medicineSchedule);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/medicines-schedule/{id}", methods={"DELETE"})
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
        $medicineSchedule = $this->medicineScheduleRepository->findOneById($id);
        if (!$medicineSchedule) {
            throw new Exception\EntityNotFoundException("MedicineSchedule with ID {$id} was not found");
        }

        if ($medicineSchedule->getMedicine()->getUser()->getId() !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $clone = clone $medicineSchedule;

        $this->medicineScheduleRepository->remove($medicineSchedule);

        return $this->item($clone);
    }
}
