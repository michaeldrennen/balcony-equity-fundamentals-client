<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\EquityFundamentalsClient;

use DateTimeImmutable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use MichaelDrennen\Balcony\EquityFundamentalsClient\DTO\EquityFundamentalsDTO;
use MichaelDrennen\Balcony\EquityFundamentalsClient\Exceptions\BalconyClientException;

final class Client
{
    private readonly ClientInterface $http;

    public function __construct(
        private readonly Config $config,
        ?ClientInterface $http = null,
    ) {
        $this->http = $http ?? new GuzzleClient([
            'timeout' => $config->timeoutSeconds,
            'http_errors' => false,
        ]);
    }

    /**
     * @return EquityFundamentalsDTO[]
     */
    public function fetchFundamentals(): array
    {
        try {
            $response = $this->http->request('GET', $this->config->baseUrl, [
                'query' => ['apikey' => $this->config->apiKey],
            ]);
        } catch (GuzzleException $e) {
            throw BalconyClientException::transport($e->getMessage(), $e);
        }

        $status = $response->getStatusCode();

        if ($status === 429) {
            $retryHeader = $response->getHeaderLine('Retry-After');
            throw BalconyClientException::rateLimited(is_numeric($retryHeader) ? (int) $retryHeader : null);
        }

        if ($status >= 400) {
            throw BalconyClientException::httpError($status, (string) $response->getBody());
        }

        return $this->parse((string) $response->getBody());
    }

    /**
     * Parse an FMP ratios JSON array. Rows missing any required numeric field are skipped.
     *
     * @return EquityFundamentalsDTO[]
     */
    public function parse(string $json): array
    {
        $decoded = json_decode($json, true);

        if (!is_array($decoded) || !array_is_list($decoded)) {
            throw BalconyClientException::malformed('Equity fundamentals payload is not a JSON array.');
        }

        $rows = [];

        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            $asOf = self::parseDate($row['date'] ?? null);
            $symbol = is_string($row['symbol'] ?? null) ? trim($row['symbol']) : '';
            $trailingPe = self::parseNumber($row['priceEarningsRatio'] ?? null);
            $forwardPe = self::parseNumber($row['forwardPe'] ?? null);
            $earningsYield = self::parseNumber($row['earningsYield'] ?? null);
            $dividendYield = self::parseNumber($row['dividendYield'] ?? null);

            if ($asOf === null || $symbol === '' || $trailingPe === null || $forwardPe === null
                || $earningsYield === null || $dividendYield === null) {
                continue;
            }

            $rows[] = new EquityFundamentalsDTO(
                asOfDate: $asOf,
                symbol: $symbol,
                trailingPe: $trailingPe,
                forwardPe: $forwardPe,
                earningsYield: $earningsYield,
                dividendYield: $dividendYield,
            );
        }

        return $rows;
    }

    public static function parseNumber(mixed $raw): ?float
    {
        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        if (!is_string($raw)) {
            return null;
        }

        $trimmed = trim($raw);

        return ($trimmed !== '' && is_numeric($trimmed)) ? (float) $trimmed : null;
    }

    public static function parseDate(mixed $raw): ?DateTimeImmutable
    {
        if (!is_string($raw)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $raw);

        return ($date !== false && $date->format('Y-m-d') === $raw) ? $date : null;
    }
}
