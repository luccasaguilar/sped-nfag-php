<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Xml;

use DOMDocument;
use Lna\Sped\Nfag\Common\XmlException;

final class Dom
{
    public static function load(string $xml): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false; $dom->formatOutput = false;
        libxml_use_internal_errors(true);
        if (! $dom->loadXML($xml)) {
            $errors = array_map(fn($e) => trim($e->message), libxml_get_errors());
            libxml_clear_errors();
            throw new XmlException('Invalid XML: '.implode('; ', $errors));
        }
        return $dom;
    }
    public static function save(DOMDocument $dom): string { return (string) $dom->saveXML($dom->documentElement); }
}
