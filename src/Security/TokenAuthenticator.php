<?php declare (strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Exception\NotAuthenticatedException;
use App\Repository\UserRepository;
use App\Traits\DateUtils;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    use DateUtils;

    private UserRepository $userRepository;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(UserRepository $userRepository, JWTTokenManagerInterface $jwtManager)
    {
        $this->userRepository = $userRepository;
        $this->jwtManager = $jwtManager;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('Authorization') && $request->headers->get('Authorization');
    }

    public function getCredentials(Request $request)
    {
        return [
            'token' => $request->headers->get('Authorization'),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiToken = $credentials['token'];
        $t = new PreAuthenticatedToken(new User(), $apiToken, 'key');

        try {
            $result = $this->jwtManager->decode($t);
        } catch (\Exception $ex) {
            return null;
        }

        $username = $result['username'];

        return $this->userRepository->findOneBy(['username' => $username]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new NotAuthenticatedException(
            'Authentication Required',
            JsonResponse::HTTP_FORBIDDEN
        );
    }

    /**
     * Called when authentication is needed, but it's not sent
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @throws NotAuthenticatedException
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        throw new NotAuthenticatedException('Authentication Required', JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
