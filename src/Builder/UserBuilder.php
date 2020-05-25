<?php declare (strict_types=1);

namespace App\Builder;

use App\Entity\User;
use App\Exception\PasswordConfirmationException;
use App\Request\User\CreateUserRequest;
use App\Request\User\UpdateUserRequest;
use App\Services\JwtManagerService;
use App\Traits\DateUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserBuilder extends BaseBuilder
{
    use DateUtils;

    private User $user;
    private JwtManagerService $jwtManager;
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        JwtManagerService $jwtManager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->jwtManager = $jwtManager;
    }

    public function create(): self
    {
        $this->user = new User();
        $this->user->setRoles($this->user->getRoles());

        return $this;
    }

    /**
     * @param CreateUserRequest|UpdateUserRequest $input
     * @return UserBuilder
     * @throws PasswordConfirmationException
     */
    public function bind($input): self
    {
        if (property_exists($input, 'username') && $input->username !== null) {
            $this->setUsername($input->username);
        }

        if (property_exists($input, 'name') && $input->name !== null) {
            $this->setName($input->name);
        }

        if (property_exists($input, 'password') && $input->password !== null) {
            if ($input->password !== $input->confirmPassword) {
                throw new PasswordConfirmationException('The two passwords do not match');
            }

            $this->setPassword($input->password);
        }

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->user->setUsername($username);

        return $this;
    }

    public function setName(string $name): self
    {
        $this->user->setName($name);

        return $this;
    }

    public function setPassword(string $password): self
    {
        $password = $this->passwordEncoder->encodePassword($this->user, $password);
        $this->user->setPassword($password);

        return $this;
    }

    public function getApiKey()
    {
        return $this->jwtManager->create($this->user);
    }

    public function build(): User
    {
        return $this->user;
    }
}
