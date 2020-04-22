<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UsersController
{
    /**
     * @Route("/users", methods={"POST"})
     */
    public function register()
    {
        return new JsonResponse(["test" => "register"]);
    }

    /**
     * @Route("/users/login", methods={"POST"})
     */
    public function login()
    {
        return new JsonResponse(["test" => "login"]);
    }

    /**
     * @Route("/users/me", methods={"GET"})
     */
    public function me()
    {
        return new JsonResponse(["test" => "me"]);
    }

    /**
     * @Route("/users/{id}", methods={"PUT"})
     * @param int $id
     * @return JsonResponse
     */
    public function update(int $id)
    {
        return new JsonResponse(["test" => $id]);
    }
}
