<?php

namespace App\Request\User;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest extends BaseRequest
{
    /**
     * @Assert\NotBlank(message="Username should not be blank")
     */
    public string $username;

    /**
     * @Assert\NotBlank(message="Password should not be blank")
     */
    public string $password;

    /**
     * @Assert\NotBlank(message="ConfirmPassword should not be blank")
     */
    public string $confirmPassword;
}
