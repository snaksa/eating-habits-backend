<?php

namespace App\Controller;

use App\Builder\MealBuilder;
use App\Exception;
use App\Repository\MealRepository;
use App\Request\Meal\CreateMealRequest;
use App\Request\Meal\UpdateMealRequest;
use App\Services\FractalManager;
use App\Transformers\MealTransformer;
use Doctrine\ORM;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MealsController extends BaseController
{
    protected string $transformerClass = MealTransformer::class;
    private MealRepository $mealRepository;
    private MealBuilder $builder;

    public function __construct(MealRepository $waterSupplyRepository, MealBuilder $builder, FractalManager $fractalManager)
    {
        $this->mealRepository = $waterSupplyRepository;
        $this->builder = $builder;

        parent::__construct($fractalManager);
    }

    /**
     * @Route("/meals", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function all(Request $request)
    {
        $this->setRequest($request);

        $meals = $this->mealRepository->findAll();

        return $this->collection($meals);
    }

    /**
     * @Route("/meals/{id}", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws ORM\NonUniqueResultException
     * @throws Exception\EntityNotFoundException
     */
    public function single(Request $request, int $id)
    {
        $this->setRequest($request);

        $meal = $this->mealRepository->findOneById($id);
        if (!$meal) {
            $this->notFound("Meal with ID {$id} was not found!");
        }

        return $this->item($meal);
    }

    /**
     * @Route("/meals", methods={"POST"})
     * @param CreateMealRequest $request
     * @return JsonResponse
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     * @throws Exception\InvalidMealTypeException
     */
    public function create(CreateMealRequest $request)
    {
        $this->setRequest($request->getRequest());

        $meal = $this->builder
            ->create()
            ->bind($request)
            ->build();

        $this->mealRepository->save($meal);

        return $this->item($meal);
    }

    /**
     * @Route("/meals/{id}", methods={"POST"})
     * @param UpdateMealRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws Exception\InvalidDateException
     * @throws Exception\InvalidMealTypeException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function update(UpdateMealRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        $meal = $this->mealRepository->findOneById($id);
        if (!$meal) {
            throw new Exception\EntityNotFoundException("Meal with ID {$id} was not found");
        }

        $meal = $this->builder
            ->setMeal($meal)
            ->bind($request)
            ->build();

        $this->mealRepository->save($meal);

        return $this->item($meal);
    }

    /**
     * @Route("/meals/{id}", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     * @throws Exception\EntityNotFoundException
     * @throws ORM\NonUniqueResultException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function delete(int $id)
    {
        $meal = $this->mealRepository->findOneById($id);
        if (!$meal) {
            throw new Exception\EntityNotFoundException("Meal with ID {$id} was not found");
        }

        $clone = clone $meal;

        $this->mealRepository->remove($meal);

        return $this->item($clone);
    }
}
