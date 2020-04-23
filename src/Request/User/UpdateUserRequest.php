<?php

namespace App\Request\User;

use App\Request\BaseRequest;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateUserRequest extends BaseRequest
{
    public ?string $name = null;
    public ?string $password = null;
    public ?string $confirmPassword = null;
}
