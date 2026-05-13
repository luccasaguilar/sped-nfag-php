<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Xml;

use InvalidArgumentException;
use Lna\Sped\Nfag\Common\XmlException;

/**
 * Valida XMLs da NFAg contra os schemas XSD oficiais.
 *
 * Sabe mapear o elemento raiz para o XSD correto, mas também aceita
 * o caminho explícito do XSD (uso direto).
 */
final class Validator
{
    /**
     * Mapeamento: elemento raiz => XSD file dentro do diretório de schemes.
     */
    public const SCHEMA_MAP = [
        'NFAg'                 => 'nfag_v1.00.xsd',
        'nfagProc'             => 'procNFAg_v1.00.xsd',
        'consSitNFAg'          => 'consSitNFAg_v1.00.xsd',
        'retConsSitNFAg'       => 'retConsSitNFAg_v1.00.xsd',
        'consStatServNFAg'     => 'consStatServNFAg_v1.00.xsd',
        'retConsStatServNFAg'  => 'retConsStatServNFAg_v1.00.xsd',
        'eventoNFAg'           => 'eventoNFAg_v1.00.xsd',
        'retEventoNFAg'        => 'retEventoNFAg_v1.00.xsd',
        'procEventoNFAg'       => 'procEventoNFAg_v1.00.xsd',
        'envEvento'            => 'eventoNFAg_v1.00.xsd',
    ];

    /**
     * Valida $xml contra $xsdPath (caminho absoluto para o XSD).
     * Retorna lista de mensagens de erro (vazia se válido).
     */
    public function validate(string $xml, string $xsdPath): array
    {
        if (! is_file($xsdPath)) {
            throw new XmlException("XSD não encontrado: {$xsdPath}");
        }
        $dom = Dom::load($xml);
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if ($dom->schemaValidate($xsdPath)) {
            libxml_clear_errors();
            return [];
        }
        $errors = array_map(
            static fn($e) => sprintf('Linha %d: %s', $e->line, trim($e->message)),
            libxml_get_errors()
        );
        libxml_clear_errors();
        return $errors;
    }

    /**
     * Valida descobrindo o XSD automaticamente a partir do elemento raiz.
     * $schemesDir é o diretório que contém os XSDs.
     */
    public function validateAuto(string $xml, string $schemesDir): array
    {
        $dom = Dom::load($xml);
        $root = $dom->documentElement?->localName ?? '';
        if ($root === '' || ! isset(self::SCHEMA_MAP[$root])) {
            throw new InvalidArgumentException("Sem XSD mapeado para o elemento raiz '{$root}'.");
        }
        $xsd = rtrim($schemesDir, '/\\') . DIRECTORY_SEPARATOR . self::SCHEMA_MAP[$root];
        return $this->validate($xml, $xsd);
    }

    /**
     * Conveniência: nome do arquivo XSD para um dado elemento raiz.
     */
    public static function schemaFor(string $rootElement): string
    {
        if (! isset(self::SCHEMA_MAP[$rootElement])) {
            throw new InvalidArgumentException("Sem XSD mapeado para '{$rootElement}'.");
        }
        return self::SCHEMA_MAP[$rootElement];
    }
}
