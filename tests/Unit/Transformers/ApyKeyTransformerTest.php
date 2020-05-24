<?php

namespace App\Tests\Unit\Transformers;

use App\Transformers\ApiKeyTransformer;
use PHPUnit\Framework\TestCase;

class ApyKeyTransformerTest extends TestCase
{
    public function testTransform()
    {
        $expected = [
            'token' => 'apiToken',
            'expiresIn' => 3600,
        ];

        $transformer = new ApiKeyTransformer();
        $this->assertEquals($expected, $transformer->transform($expected));
    }
}
