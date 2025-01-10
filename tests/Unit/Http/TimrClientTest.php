<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
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

    // Mock die HTTP-Antwort
    $responseMock = Mockery::mock(Response::class);
    $responseMock->shouldReceive('getBody->getContents')
        ->once()
        ->andReturn(json_encode($expectedResponse));

    $this->client->shouldReceive('get')
        ->with('test-endpoint', [
            'headers' => [
                'Authorization' => 'Bearer test-token',
                'Accept' => 'application/json',
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

    $this->client->shouldReceive('get')
        ->once()
        ->andReturn($responseMock);

    expect(fn () => $this->timrClient->get('test-endpoint'))
        ->toThrow(TimrException::class, 'Timr API request failed: Syntax error');
});

test('handles network error', function () {
    $request = new Request('GET', 'test-endpoint');
    $this->client->shouldReceive('get')
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

    $this->client->shouldReceive('get')
        ->once()
        ->andReturn($responseMock);

    expect(fn () => $this->timrClient->get('test-endpoint'))
        ->toThrow(TimrException::class);
});

test('creates client with default configuration when no client provided', function () {
    // Nutze den FakeTokenProvider für die Typprüfung
    $tokenProvider = new FakeTokenProvider;
    $baseUrl = 'https://api.timr.com';

    // Erstelle den TimrClient mit dem korrekten Typ
    $timrClient = new TimrClient($tokenProvider, $baseUrl);

    expect($timrClient)->toBeInstanceOf(TimrClient::class);
});
