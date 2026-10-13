<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\EquityFundamentalsClient\Models;

use Illuminate\Database\Eloquent\Model;

final class EquityFundamentalsSnapshot extends Model
{
    protected $table = 'balcony_equity_fundamentals_snapshots';

    protected $guarded = ['id'];

    protected $casts = [
        'as_of_date' => 'date',
        'trailing_pe' => 'decimal:4',
        'forward_pe' => 'decimal:4',
        'forward_earnings_yield' => 'decimal:4',
        'dividend_yield' => 'decimal:4',
    ];
}
