<?php declare (strict_types=1);

namespace App\Services;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class JwtManagerService
{
    private JWTTokenManagerInterface $manager;

    public function __construct(JWTTokenManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function decode(PreAuthenticatedToken $token)
    {
        return $this->manager->decode($token);
    }

    public function create(User $user)
    {
        return $this->manager->create($user);
    }
}
