# balcony-equity-fundamentals-client

PHP 8.1+ client for equity index valuation ratios (Financial Modeling Prep), part of The Balcony macro flow system.

## Usage (framework-agnostic)

```php
use MichaelDrennen\Balcony\EquityFundamentalsClient\Client;
use MichaelDrennen\Balcony\EquityFundamentalsClient\Config;

$client = new Client(new Config(apiKey: 'YOUR_FMP_KEY'));

foreach ($client->fetchFundamentals() as $dto) {
    // $dto->asOfDate, $dto->symbol, $dto->trailingPe, $dto->forwardPe, $dto->earningsYield, $dto->dividendYield
}
```

Rows missing any required numeric field are skipped.

## Laravel persistence

```php
use MichaelDrennen\Balcony\EquityFundamentalsClient\Models\EquityFundamentalsSnapshot;

foreach ($client->fetchFundamentals() as $dto) {
    EquityFundamentalsSnapshot::updateOrCreate(
        ['index_ticker' => $dto->symbol, 'as_of_date' => $dto->asOfDate->format('Y-m-d')],
        $dto->asModelAttributes(),
    );
}
```

## Errors

Failures throw `MichaelDrennen\Balcony\EquityFundamentalsClient\Exceptions\BalconyClientException`. HTTP 429 sets `isRateLimited()` and exposes `retryAfterSeconds`.

## Tests

```
composer install
vendor/bin/phpunit
```
