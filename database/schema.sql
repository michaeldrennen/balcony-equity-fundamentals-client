CREATE TABLE IF NOT EXISTS balcony_equity_fundamentals_snapshots (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    as_of_date DATE NOT NULL,
    index_ticker VARCHAR(16) NOT NULL,
    trailing_pe DECIMAL(8,4) NOT NULL,
    forward_pe DECIMAL(8,4) NOT NULL,
    forward_earnings_yield DECIMAL(8,4) NOT NULL,
    dividend_yield DECIMAL(8,4) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY balcony_equity_ticker_date_unique (index_ticker, as_of_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
