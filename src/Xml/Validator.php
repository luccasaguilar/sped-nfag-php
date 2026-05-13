<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Xml;

use Lna\Sped\Nfag\Common\XmlException;

final class Validator
{
    public function validate(string $xml, string $xsdPath): array
    {
        if (! is_file($xsdPath)) throw new XmlException("XSD not found: {$xsdPath}");
        $dom = Dom::load($xml); libxml_use_internal_errors(true);
        if ($dom->schemaValidate($xsdPath)) { libxml_clear_errors(); return []; }
        $errors = array_map(fn($e) => sprintf('Line %d: %s', $e->line, trim($e->message)), libxml_get_errors());
        libxml_clear_errors(); return $errors;
    }
}
