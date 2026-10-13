<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\EquityFundamentalsClient\Tests\Integration;

use DateTimeImmutable;
use MichaelDrennen\Balcony\EquityFundamentalsClient\Client;
use MichaelDrennen\Balcony\EquityFundamentalsClient\Config;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Hits the real remote source. Excluded from the default run; use `vendor/bin/phpunit --group integration`.
 */
#[Group('integration')]
final class LiveFetchTest extends TestCase
{
    private const MAX_AGE_DAYS = 120;

    public function testFetchesRecentDataFromLiveSource(): void
    {
        $key = getenv('FMP_API_KEY');

        if (!is_string($key) || trim($key) === '') {
            $this->markTestSkipped('Set FMP_API_KEY to run this live test.');
        }

        $dtos = (new Client(new Config($key)))->fetchFundamentals();

        $this->assertNotEmpty($dtos, 'Expected S&P 500 ratios from Financial Modeling Prep.');

        $newest = max(array_map(static fn ($dto): DateTimeImmutable => $dto->asOfDate, $dtos));
        $ageDays = (int) $newest->diff(new DateTimeImmutable())->format('%a');

        $this->assertLessThanOrEqual(
            self::MAX_AGE_DAYS,
            $ageDays,
            sprintf('Newest record is from %s; the source may have stopped updating.', $newest->format('Y-m-d')),
        );
    }
}
