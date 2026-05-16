<?php

declare(strict_types=1);

namespace CommonPHP\Drivers\Config\XML;

use CommonPHP\Config\Contracts\AbstractConfigDriver;
use CommonPHP\Config\Exceptions\ConfigException;
use CommonPHP\Config\Exceptions\ConfigValidationException;
use DOMDocument;
use DOMElement;
use Throwable;

final class XmlConfigurationDriver extends AbstractConfigDriver
{
    public function validate(string $data): bool
    {
        try {
            $this->decode($data);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function encode(array $config): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $root = $document->createElement('config');
        $document->appendChild($root);

        $this->appendArray($document, $root, $config);

        $xml = $document->saveXML();

        if ($xml === false) {
            throw new ConfigException('Could not encode XML configuration data.');
        }

        return $xml;
    }

    public function decode(string $data): array
    {
        if (stripos($data, '<!DOCTYPE') !== false) {
            throw new ConfigValidationException('XML configuration data must not contain a DOCTYPE declaration.');
        }

        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $document = new DOMDocument();

        try {
            $loaded = $document->loadXML($data, LIBXML_NONET | LIBXML_NOBLANKS);
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (!$loaded) {
            $message = 'Invalid XML configuration data.';

            if (!empty($errors)) {
                $message .= ' ' . trim($errors[0]->message);
            }

            throw new ConfigValidationException($message);
        }

        $root = $document->documentElement;

        if (!$root instanceof DOMElement || $root->tagName !== 'config') {
            throw new ConfigValidationException('XML configuration data must have a <config> root element.');
        }

        return $this->decodeArray($root);
    }

    private function appendArray(DOMDocument $document, DOMElement $parent, array $values): void
    {
        foreach ($values as $key => $value) {
            $entry = $document->createElement('entry');
            $entry->setAttribute('key', (string) $key);

            if (is_array($value)) {
                $entry->setAttribute('type', 'array');
                $this->appendArray($document, $entry, $value);
            } elseif (is_string($value)) {
                $entry->setAttribute('type', 'string');
                $entry->appendChild($document->createTextNode($value));
            } elseif (is_int($value)) {
                $entry->setAttribute('type', 'integer');
                $entry->appendChild($document->createTextNode((string) $value));
            } elseif (is_float($value)) {
                $entry->setAttribute('type', 'float');
                $entry->appendChild($document->createTextNode((string) $value));
            } elseif (is_bool($value)) {
                $entry->setAttribute('type', 'boolean');
                $entry->appendChild($document->createTextNode($value ? 'true' : 'false'));
            } elseif ($value === null) {
                $entry->setAttribute('type', 'null');
            } else {
                throw new ConfigException('Unsupported XML configuration value type: ' . get_debug_type($value));
            }

            $parent->appendChild($entry);
        }
    }

    private function decodeArray(DOMElement $parent): array
    {
        $values = [];

        foreach ($parent->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            if ($node->tagName !== 'entry') {
                throw new ConfigValidationException('XML configuration arrays may only contain <entry> elements.');
            }

            if (!$node->hasAttribute('key')) {
                throw new ConfigValidationException('XML configuration entry is missing a key attribute.');
            }

            $key = $this->decodeKey($node->getAttribute('key'));
            $values[$key] = $this->decodeEntry($node);
        }

        return $values;
    }

    private function decodeEntry(DOMElement $entry): mixed
    {
        $type = $entry->getAttribute('type');

        if ($type === '') {
            $type = 'string';
        }

        return match ($type) {
            'array' => $this->decodeArray($entry),
            'string' => $entry->textContent,
            'integer' => $this->decodeInteger($entry->textContent),
            'float' => $this->decodeFloat($entry->textContent),
            'boolean' => $this->decodeBoolean($entry->textContent),
            'null' => null,
            default => throw new ConfigValidationException('Unsupported XML configuration entry type: ' . $type),
        };
    }

    private function decodeKey(string $key): int|string
    {
        if (preg_match('/^-?(0|[1-9][0-9]*)$/', $key) === 1 && (string) ((int) $key) === $key) {
            return (int) $key;
        }

        return $key;
    }

    private function decodeInteger(string $value): int
    {
        $value = trim($value);

        if (preg_match('/^-?(0|[1-9][0-9]*)$/', $value) !== 1) {
            throw new ConfigValidationException('Invalid XML integer configuration value: ' . $value);
        }

        return (int) $value;
    }

    private function decodeFloat(string $value): float
    {
        $value = trim($value);

        if (!is_numeric($value)) {
            throw new ConfigValidationException('Invalid XML float configuration value: ' . $value);
        }

        return (float) $value;
    }

    private function decodeBoolean(string $value): bool
    {
        $value = strtolower(trim($value));

        return match ($value) {
            'true', '1' => true,
            'false', '0' => false,
            default => throw new ConfigValidationException('Invalid XML boolean configuration value: ' . $value),
        };
    }
}