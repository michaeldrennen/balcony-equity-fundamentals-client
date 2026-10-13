<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('balcony_equity_fundamentals_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->date('as_of_date');
            $table->string('index_ticker', 16);
            $table->decimal('trailing_pe', 8, 4);
            $table->decimal('forward_pe', 8, 4);
            $table->decimal('forward_earnings_yield', 8, 4);
            $table->decimal('dividend_yield', 8, 4);
            $table->timestamps();

            $table->unique(['index_ticker', 'as_of_date'], 'balcony_equity_ticker_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balcony_equity_fundamentals_snapshots');
    }
};
