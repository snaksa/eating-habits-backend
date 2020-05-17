<?php

namespace App\Controller;

use App\Builder\UserBuilder;
use App\Repository\UserRepository;
use App\Request\User\CreateUserRequest;
use App\Request\User\LoginUserRequest;
use App\Request\User\UpdateUserRequest;
use App\Services\AuthorizationService;
use App\Services\FractalManager;
use App\Transformers\ApiKeyTransformer;
use App\Transformers\UserTransformer;
use App\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends BaseController
{
    protected string $transformerClass = UserTransformer::class;
    private UserRepository $userRepository;
    private UserBuilder $builder;
    private AuthorizationService $authService;

    public function __construct(
        UserRepository $repository,
        UserBuilder $builder,
        AuthorizationService $authService,
        FractalManager $fractal
    ) {
        $this->userRepository = $repository;
        $this->builder = $builder;
        $this->authService = $authService;

        parent::__construct($fractal);
    }

    /**
     * @Route("/users", methods={"POST"})
     * @param CreateUserRequest $request
     * @return JsonResponse
     * @throws Exception\PasswordConfirmationException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function register(CreateUserRequest $request)
    {
        $this->setRequest($request->getRequest());

        $user = $this->builder
            ->create()
            ->bind($request)
            ->build();

        $this->userRepository->save($user);

        return $this->item($user);
    }

    /**
     * @Route("/users/login", methods={"POST"})
     * @param LoginUserRequest $request
     * @return JsonResponse
     * @throws Exception\InvalidPasswordException
     */
    public function login(LoginUserRequest $request)
    {
        $user = $this->userRepository->findOneBy([
            'username' => $request->username
        ]);

        $this->authService->isPasswordValid($user, $request->password);

        $token = $this->builder
            ->setUser($user)
            ->getApiKey();

        return $this->item(['token' => $token, 'expiresIn' => 108000], ApiKeyTransformer::class);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/users/me", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws Exception\NotAuthenticatedException
     */
    public function me(Request $request)
    {
        $this->setRequest($request);
        return $this->item($this->authService->getCurrentUser());
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/users/{id}", methods={"POST"})
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     * @throws Exception\NotAuthenticatedException
     * @throws Exception\NotAuthorizedException
     * @throws Exception\PasswordConfirmationException
     * @throws ORM\ORMException
     * @throws ORM\OptimisticLockException
     */
    public function update(UpdateUserRequest $request, int $id)
    {
        $this->setRequest($request->getRequest());

        if ($id !== $this->authService->getCurrentUser()->getId()) {
            throw new Exception\NotAuthorizedException('You do not have permissions to access this resource');
        }

        $user = $this->builder
            ->setUser($this->authService->getCurrentUser())
            ->bind($request)
            ->build();

        $this->userRepository->save($user);

        return $this->item($user);
    }
}
