<?php

namespace App\Request;

use App\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestResolver implements ArgumentValueResolverInterface
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return is_subclass_of($argument->getType(), BaseRequest::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $class = $argument->getType();
        $object = new $class($request);
        $errors = $this->validator->validate($object);

        if(count($errors)) {
            $all = [];
            foreach ($errors as $key => $error) {
                $all[] = $error->getMessage();
            }

            throw new InvalidDataException($all);
        }

        yield new $class($request);
    }
}
