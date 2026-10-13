<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\EquityFundamentalsClient\DTO;

use DateTimeImmutable;

final class EquityFundamentalsDTO implements MetricObservation
{
    public function __construct(
        public readonly DateTimeImmutable $asOfDate,
        public readonly string $symbol,
        public readonly float $trailingPe,
        public readonly float $forwardPe,
        public readonly float $earningsYield,
        public readonly float $dividendYield,
    ) {
    }

    public function asModelAttributes(): array
    {
        return [
            'as_of_date' => $this->asOfDate->format('Y-m-d'),
            'index_ticker' => $this->symbol,
            'trailing_pe' => $this->trailingPe,
            'forward_pe' => $this->forwardPe,
            'forward_earnings_yield' => $this->earningsYield,
            'dividend_yield' => $this->dividendYield,
        ];
    }
}
