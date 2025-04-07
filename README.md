# Commission Calculation Project

This is a Symfony-based project designed to calculate commissions for transactions from a provided CSV file. It uses the **PSR-12** coding standard and has unit and feature tests to ensure the functionality of the system.

## Requirements

- PHP 8.3
- The following PHP extensions are required:
  - Ctype
  - Iconv
  - PCRE
  - Session
  - SimpleXML
  - Tokenizer
- Composer (dependency manager for PHP)

## Installation

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd <project-directory>

2. **Install project dependencies: Run the following command to install all necessary dependencies using Composer:**
    ```bash
    composer install --dev
    ```
3. **Set up environment variables:** Ensure you have a .env.local file with the appropriate configuration for the API. If it doesn’t exist, create one with the following content
    ```
    EXCHANGE_RATES_API_URL=https://api.exchangeratesapi.io/latest
    EXCHANGE_RATES_API_KEY=YOUR_API_KEY
    ```

## Running the application

To calculate commissions for transactions from a CSV file, run the following command:

    ```
    php bin/console app:calculate-commissions example-data.csv
    ```

## Testing

The project uses PHPUnit for testing and PHP_CodeSniffer for code style checks.

### Run Code Style Check

To check that your code adheres to the PSR-12 coding standard, run:

```bash
vendor/bin/phpcs --standard=PSR12 src
```

### Run Unit Tests

To run unit tests, execute:

```bash
vendor/bin/phpunit tests/Unit
```

### Run Feature Tests

To run feature tests, execute:

```bash
vendor/bin/phpunit tests/Feature
```
