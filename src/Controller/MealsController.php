<?php

namespace App\Controller;

use App\Exception\EntityNotFoundException;
use App\Repository\MealRepository;
use App\Request\Meal\CreateMealRequest;
use App\Response\ErrorResponse;
use App\Services\FractalManager;
use App\Transformers\MealTransformer;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MealsController extends BaseController
{
    protected string $transformerClass = MealTransformer::class;
    public MealRepository $mealRepository;

    public function __construct(MealRepository $mealRepository, FractalManager $fractalManager)
    {
        $this->mealRepository = $mealRepository;

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
     * @throws NonUniqueResultException
     * @throws EntityNotFoundException
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
     */
    public function create(CreateMealRequest $request)
    {
        return new JsonResponse(["test" => $request->description]);
    }

    /**
     * @Route("/meals/{id}", methods={"PUT"})
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id)
    {
        return new JsonResponse(["test" => $id]);
    }

    /**
     * @Route("/meals/{id}", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id)
    {
        return new JsonResponse(["test" => $id]);
    }
}
