<?php

declare(strict_types=1);

namespace App\Tests\Feature\Trait;

trait ResponseAssertions
{
    protected function assertSuccessfulResponse(array $decodedResponse): void
    {
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponse();
        $this->assertEquals('success', $decodedResponse['status']);
        $this->assertArrayHasKey('data', $decodedResponse);
    }

    protected function assertFailureResponse(array $decodedResponse): void
    {
        $this->assertEquals('fail', $decodedResponse['status']);
        $this->assertArrayHasKey('data', $decodedResponse);
    }

    protected function assertValidationErrorResponse(array $decodedResponse): void
    {
        $this->assertFailureResponse($decodedResponse);
        $this->assertEquals('Validation Error', $decodedResponse['data']['message']);
        $this->assertArrayHasKey('errors', $decodedResponse['data']);
    }

    protected function assertJsonResponse(): void
    {
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
    }

    public function assertPathIsProtected(string $path, string $method): void
    {
        $responseData = $this->getFailureResponseData($this->client, $method, $path, expectedCode: 401);
        $this->assertEquals('Unauthorized', $responseData['message']);
    }
}