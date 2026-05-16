# CommonPHP XML Config Driver

Configuration driver for CommonPHP that encodes and decodes the CommonPHP XML configuration format.

## Requirements

- PHP `^8.5`
- PHP DOM extension `ext-dom`
- `comphp/config:^0.3`

## Installation

Once this package is available through your Composer repositories, install it with:

```bash
composer require comphp/config-xml
```

## Usage

```php
<?php

use CommonPHP\Drivers\Config\XML\XmlConfigurationDriver;

$driver = new XmlConfigurationDriver();

$config = [
    'app' => 'demo',
    'debug' => true,
    'database' => [
        'host' => 'localhost',
    ],
];

$xml = $driver->encode($config);
$decoded = $driver->decode($xml);

$driver->write(__DIR__ . '/config.xml', $config);
$fromFile = $driver->read(__DIR__ . '/config.xml');
```

## Format Notes

This package uses the CommonPHP XML config format with a `<config>` root and typed `<entry>` elements. It is not an arbitrary XML-to-array converter. DOCTYPE declarations are rejected.

## Error Handling

Read, write, parse, validation, and unsupported value failures throw CommonPHP config exceptions such as `ConfigReadException`, `ConfigWriteException`, `ConfigValidationException`, or `ConfigException`.

## Documentation

- [Usage](docs/usage.md)
- [Testing](TESTING.md)
- [Contributing](CONTRIBUTING.md)
- [Security](SECURITY.md)

## License

MIT. See [LICENSE.md](LICENSE.md).
