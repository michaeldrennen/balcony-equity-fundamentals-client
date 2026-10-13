<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\EquityFundamentalsClient;

use InvalidArgumentException;

final class Config
{
    public const DEFAULT_BASE_URL = 'https://financialmodelingprep.com/api/v3/ratios/%5ESPX';

    public function __construct(
        public readonly string $apiKey,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly float $timeoutSeconds = 15.0,
    ) {
        if (trim($apiKey) === '') {
            throw new InvalidArgumentException('Financial Modeling Prep API key is required.');
        }
    }
}
