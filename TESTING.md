# Testing

## Required dev dependencies

This package uses PHPUnit 13 for its test suite. `composer.json` already lists:

- `phpunit/phpunit:^13.1`

If PHPUnit is missing from a clone, install it with:

```bash
composer require --dev phpunit/phpunit:^13.1
```

## Running tests

Install dependencies for this repository, then run PHPUnit from this repository root:

```bash
composer install
vendor/bin/phpunit
```

On Windows, use `vendor\bin\phpunit.bat`.

## Notes

The tests create temporary files and directories under PHP's system temp directory and remove them during teardown. Read-only file permission tests are skipped on Windows because chmod-style readability and writability checks are not enforced consistently there.

The suite covers XML validation, encoding, decoding the CommonPHP `<config>` format, invalid roots, unsupported entry types, DOCTYPE rejection, read failures, and write failures.
