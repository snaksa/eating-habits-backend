<?php

namespace App\Tests\Unit\Response;

use App\Response\ErrorResponse;
use App\Traits\DateUtils;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorResponseTest extends TestCase
{
    use DateUtils;

    public function testDefault()
    {
        $response = new ErrorResponse();
        $body = [
            'error' => [
                'type' => '',
                'message' => null,
                'status' => 400
            ]
        ];

        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($body, json_decode($response->getContent(), true));
    }

    public function testDefaultStatusCode()
    {
        $response = new ErrorResponse(null, 0);
        $body = [
            'error' => [
                'type' => '',
                'message' => null,
                'status' => 400
            ]
        ];

        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($body, json_decode($response->getContent(), true));
    }

    public function testErrorType()
    {
        $response = new ErrorResponse(null, 0, 'InvalidDateException');
        $body = [
            'error' => [
                'type' => 'InvalidDateException',
                'message' => null,
                'status' => 400
            ]
        ];

        $this->assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($body, json_decode($response->getContent(), true));
    }

    public function testErrorStatus()
    {
        $response = new ErrorResponse(null, 403);
        $body = [
            'error' => [
                'type' => '',
                'message' => null,
                'status' => 403
            ]
        ];

        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals($body, json_decode($response->getContent(), true));
    }

    public function testMessage()
    {
        $response = new ErrorResponse('Invalid date', 403);
        $body = [
            'error' => [
                'type' => '',
                'message' => 'Invalid date',
                'status' => 403
            ]
        ];

        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals($body, json_decode($response->getContent(), true));
    }
}
