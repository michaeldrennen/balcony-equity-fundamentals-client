<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\EquityFundamentalsClient\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use MichaelDrennen\Balcony\EquityFundamentalsClient\Client;
use MichaelDrennen\Balcony\EquityFundamentalsClient\Config;
use MichaelDrennen\Balcony\EquityFundamentalsClient\Exceptions\BalconyClientException;
use PHPUnit\Framework\TestCase;

final class ClientTest extends TestCase
{
    private function fixture(): string
    {
        return (string) file_get_contents(__DIR__ . '/Fixtures/sample_response.json');
    }

    public function testParsesCompleteRowsOnly(): void
    {
        $dtos = (new Client(new Config('key')))->parse($this->fixture());

        $this->assertCount(1, $dtos);
        $this->assertSame('^SPX', $dtos[0]->symbol);
        $this->assertSame(25.45, $dtos[0]->trailingPe);
        $this->assertSame(0.0392, $dtos[0]->earningsYield);
    }

    public function testFetchUsesMockedHttp(): void
    {
        $http = new GuzzleClient([
            'handler' => HandlerStack::create(new MockHandler([new Response(200, [], $this->fixture())])),
        ]);

        $this->assertCount(1, (new Client(new Config('key'), $http))->fetchFundamentals());
    }

    public function testRateLimitThrows(): void
    {
        $http = new GuzzleClient([
            'handler' => HandlerStack::create(new MockHandler([new Response(429, ['Retry-After' => '2'], '')])),
            'http_errors' => false,
        ]);

        try {
            (new Client(new Config('key'), $http))->fetchFundamentals();
            $this->fail('Expected BalconyClientException.');
        } catch (BalconyClientException $e) {
            $this->assertTrue($e->isRateLimited());
            $this->assertSame(2, $e->retryAfterSeconds);
        }
    }

    public function testNonArrayPayloadThrows(): void
    {
        $this->expectException(BalconyClientException::class);
        (new Client(new Config('key')))->parse('{"error":"x"}');
    }

    public function testEmptyKeyRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config('  ');
    }
}
