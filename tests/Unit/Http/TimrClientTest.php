<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PhilHarmonie\Timr\Contracts\TokenProviderInterface;
use PhilHarmonie\Timr\Exceptions\TimrException;
use PhilHarmonie\Timr\Http\TimrClient;

final class FakeTokenProvider implements TokenProviderInterface
{
    public function getToken(): string
    {
        return 'test-token';
    }
}

beforeEach(function () {
    $this->tokenProvider = new FakeTokenProvider;
    $this->client = Mockery::mock(Client::class);

    $this->timrClient = new TimrClient($this->tokenProvider, 'https://api.timr.com', $this->client);
});

test('gets data successfully', function () {
    $expectedResponse = ['data' => ['test' => true]];

    $responseMock = Mockery::mock(Response::class);
    $responseMock->shouldReceive('getBody->getContents')
        ->once()
        ->andReturn(json_encode($expectedResponse));

    $this->client->shouldReceive('request')
        ->with('GET', 'test-endpoint', [
            'headers' => [
                'Authorization' => 'Bearer test-token',
                'Accept' => 'application/json, application/problem+json',
            ],
            'query' => ['param' => 'value'],
        ])
        ->once()
        ->andReturn($responseMock);

    $result = $this->timrClient->get('test-endpoint', ['param' => 'value']);

    expect($result)->toBe($expectedResponse);
});

test('handles empty response', function () {
    $responseMock = Mockery::mock(Response::class);
    $responseMock->shouldReceive('getBody->getContents')
        ->once()
        ->andReturn('');

    $this->client->shouldReceive('request')
        ->with('GET', 'test-endpoint', Mockery::any())
        ->once()
        ->andReturn($responseMock);

    expect(fn () => $this->timrClient->get('test-endpoint'))
        ->toThrow(TimrException::class, 'Timr API request failed: Syntax error');
});

test('handles network error', function () {
    $request = new Request('GET', 'test-endpoint');

    $this->client->shouldReceive('request')
        ->with('GET', 'test-endpoint', Mockery::any())
        ->once()
        ->andThrow(new RequestException('Network error', $request));

    expect(fn () => $this->timrClient->get('test-endpoint'))
        ->toThrow(TimrException::class, 'Timr API request failed: Network error');
});

test('handles malformed json', function () {
    $responseMock = Mockery::mock(Response::class);
    $responseMock->shouldReceive('getBody->getContents')
        ->once()
        ->andReturn('{"invalid": json}');

    $this->client->shouldReceive('request')
        ->with('GET', 'test-endpoint', Mockery::any())
        ->once()
        ->andReturn($responseMock);

    expect(fn () => $this->timrClient->get('test-endpoint'))
        ->toThrow(TimrException::class, 'Timr API request failed: Syntax error');
});

test('creates client with default configuration when no client provided', function () {
    $tokenProvider = new FakeTokenProvider;
    $baseUrl = 'https://api.timr.com';

    $timrClient = new TimrClient($tokenProvider, $baseUrl);

    expect($timrClient)->toBeInstanceOf(TimrClient::class);
});

test('turns an RFC 9457 problem response into a TimrException', function () {
    $problem = [
        'type' => 'https://errors.timr.com/validation',
        'title' => 'Bad Request',
        'status' => 422,
        'detail' => 'Request validation failed',
        'instance' => '/project-times',
        'trace_id' => '9f4d1e70b83a26c5',
        'errors' => [
            ['detail' => 'must not be null', 'field' => 'start', 'type' => 'https://errors.timr.com/validation/not-null'],
        ],
    ];

    $request = new Request('PATCH', 'project-times/1');
    $response = new Response(422, ['Content-Type' => 'application/problem+json'], json_encode($problem));

    $this->client->shouldReceive('request')
        ->with('PATCH', 'project-times/1', Mockery::any())
        ->once()
        ->andThrow(new ClientException('Client error', $request, $response));

    try {
        $this->timrClient->patch('project-times/1', ['status' => 'cleared']);
        $this->fail('Expected a TimrException');
    } catch (TimrException $e) {
        expect($e->statusCode)->toBe(422)
            ->and($e->type)->toBe('https://errors.timr.com/validation')
            ->and($e->title)->toBe('Bad Request')
            ->and($e->detail)->toBe('Request validation failed')
            ->and($e->instance)->toBe('/project-times')
            ->and($e->traceId)->toBe('9f4d1e70b83a26c5')
            ->and($e->errors)->toHaveCount(1)
            ->and($e->getMessage())->toContain('Request validation failed')
            ->and($e->getMessage())->toContain('9f4d1e70b83a26c5');
    }
});

test('falls back to the guzzle message when the error body is not a problem document', function () {
    $request = new Request('GET', 'project-times');
    $response = new Response(503, [], 'service unavailable');

    $this->client->shouldReceive('request')
        ->with('GET', 'project-times', Mockery::any())
        ->once()
        ->andThrow(new ServerException('Server error', $request, $response));

    try {
        $this->timrClient->get('project-times');
        $this->fail('Expected a TimrException');
    } catch (TimrException $e) {
        expect($e->statusCode)->toBe(503)
            ->and($e->traceId)->toBeNull()
            ->and($e->getMessage())->toContain('Server error');
    }
});
