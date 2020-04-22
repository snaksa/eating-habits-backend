<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorResponse extends JsonResponse
{
    public function __construct($message = null, int $status = JsonResponse::HTTP_BAD_REQUEST, string $type = '')
    {
        if($status === 0) {
            $status = JsonResponse::HTTP_BAD_REQUEST;
        }

        $error = [
            'error' => [
                'type' => $type,
                'message' => $message,
                'status' => $status
            ]
        ];

        parent::__construct($error, $status);
    }
}
