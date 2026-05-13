<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Lna\Sped\Nfag\Common\XmlException;

/**
 * Geração dos XMLs "Proc" (NFAg + protocolo, evento + retorno) conforme
 * procNFAg_v1.00.xsd e procEventoNFAg_v1.00.xsd.
 */
final class Complements
{
    public const NS = 'http://www.portalfiscal.inf.br/nfag';

    /**
     * Compõe o nfagProc (procNFAg) a partir do XML assinado da NFAg e
     * do XML de resposta (com <protNFAg>).
     */
    public static function toAuthorize(string $signedNfagXml, string $responseXml, string $version = '1.00'): string
    {
        $prot = self::extractFirst($responseXml, 'protNFAg');
        if ($prot === null) {
            throw new XmlException('protNFAg não encontrado no XML de resposta.');
        }
        $domNfag = self::load($signedNfagXml);
        if ($domNfag->documentElement?->localName !== 'NFAg') {
            throw new XmlException('XML de entrada não é uma NFAg (raiz != NFAg).');
        }

        $proc = new DOMDocument('1.0', 'UTF-8');
        $proc->preserveWhiteSpace = false;
        $proc->formatOutput = false;
        $root = $proc->createElement('nfagProc');
        $root->setAttribute('xmlns', self::NS);
        $root->setAttribute('versao', $version);
        $root->appendChild($proc->importNode($domNfag->documentElement, true));
        $root->appendChild($proc->importNode($prot, true));
        $proc->appendChild($root);
        return (string) $proc->saveXML($proc->documentElement);
    }

    /**
     * Compõe o procEventoNFAg a partir do evento assinado e do
     * retorno do webservice (com <retEventoNFAg>).
     */
    public static function toAuthorizeEvent(string $signedEventXml, string $retEventXml, string $version = '1.00'): string
    {
        $ret = self::extractFirst($retEventXml, 'retEventoNFAg');
        if ($ret === null) {
            throw new XmlException('retEventoNFAg não encontrado no XML de retorno.');
        }
        $domEvt = self::load($signedEventXml);
        if ($domEvt->documentElement?->localName !== 'eventoNFAg') {
            throw new XmlException('XML de entrada não é um eventoNFAg.');
        }

        $proc = new DOMDocument('1.0', 'UTF-8');
        $proc->preserveWhiteSpace = false;
        $proc->formatOutput = false;
        $root = $proc->createElement('procEventoNFAg');
        $root->setAttribute('xmlns', self::NS);
        $root->setAttribute('versao', $version);
        $root->appendChild($proc->importNode($domEvt->documentElement, true));
        $root->appendChild($proc->importNode($ret, true));
        $proc->appendChild($root);
        return (string) $proc->saveXML($proc->documentElement);
    }

    private static function load(string $xml): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        if (! $dom->loadXML($xml)) {
            throw new XmlException('XML inválido.');
        }
        return $dom;
    }

    private static function extractFirst(string $xml, string $localName): ?DOMElement
    {
        $dom = self::load($xml);
        $xp = new DOMXPath($dom);
        $node = $xp->query('//*[local-name()="' . $localName . '"]')->item(0);
        return $node instanceof DOMElement ? $node : null;
    }
}
