<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\EquityFundamentalsClient\Tests;

use DateTimeImmutable;
use MichaelDrennen\Balcony\EquityFundamentalsClient\DTO\EquityFundamentalsDTO;
use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase
{
    public function testDtoMapsToModelAttributes(): void
    {
        $dto = new EquityFundamentalsDTO(new DateTimeImmutable('2026-10-09'), '^SPX', 25.45, 21.30, 0.0392, 0.0135);

        $this->assertSame([
            'as_of_date' => '2026-10-09',
            'index_ticker' => '^SPX',
            'trailing_pe' => 25.45,
            'forward_pe' => 21.30,
            'forward_earnings_yield' => 0.0392,
            'dividend_yield' => 0.0135,
        ], $dto->asModelAttributes());
    }

    public function testEloquentModelAcceptsDtoAttributes(): void
    {
        if (!class_exists('Illuminate\Database\Eloquent\Model')) {
            $this->markTestSkipped('illuminate/database is not installed.');
        }

        // Eloquent's guarded-attribute check reads the column listing, so the model needs a connection.
        $capsule = new \Illuminate\Database\Capsule\Manager();
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $dto = new EquityFundamentalsDTO(new DateTimeImmutable('2026-10-09'), '^SPX', 1.0, 2.0, 3.0, 4.0);
        $model = new \MichaelDrennen\Balcony\EquityFundamentalsClient\Models\EquityFundamentalsSnapshot($dto->asModelAttributes());

        $this->assertSame('^SPX', $model->index_ticker);
        $this->assertSame('balcony_equity_fundamentals_snapshots', $model->getTable());
    }
}
