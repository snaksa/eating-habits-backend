<?php declare (strict_types=1);

namespace App\Services;

use App\Entity\User;
use App\Exception\InvalidPasswordException;
use App\Exception\NotAuthenticatedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthorizationService
{
    private Security $security;
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * @param Security $security
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(Security $security, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->security = $security;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @return bool
     * @throws NotAuthenticatedException
     */
    public function isLoggedIn(): bool
    {
        $isLoggedIn = $this->getCurrentUser()->getId() != '';

        return true;
    }

    /**
     * @return User
     * @throws NotAuthenticatedException
     */
    public function getCurrentUser(): User
    {
        if ($this->security->getToken() === null) {
            throw new NotAuthenticatedException('User not authenticated', JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = $this->security->getToken()->getUser();

        if ($user instanceof User) {
            return $user;
        }

        throw new NotAuthenticatedException('User not authenticated', JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * @param UserInterface|null $user
     * @param string $password
     * @throws InvalidPasswordException
     */
    public function isPasswordValid(?UserInterface $user, string $password)
    {
        if (!$user || !$this->passwordEncoder->isPasswordValid($user, $password)) {
            throw new InvalidPasswordException('Wrong username or password');
        }
    }
}
