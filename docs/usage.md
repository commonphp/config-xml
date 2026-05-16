# Usage

`comphp/config-xml` provides `CommonPHP\Drivers\Config\XML\XmlConfigurationDriver` for the CommonPHP XML config format.

## Encode and Decode

```php
use CommonPHP\Drivers\Config\XML\XmlConfigurationDriver;

$driver = new XmlConfigurationDriver();

$config = [
    'name' => 'demo',
    'database' => [
        'host' => 'localhost',
    ],
];

$data = $driver->encode($config);
$decoded = $driver->decode($data);
```

## Read and Write

```php
$driver->write(__DIR__ . '/config.xml', $config);
$config = $driver->read(__DIR__ . '/config.xml');
```

## Notes

XML configs use a `<config>` root with typed `<entry>` elements. This driver does not convert arbitrary XML documents to arrays. DOCTYPE declarations are rejected.

Failures throw CommonPHP config exceptions instead of returning `false`.
