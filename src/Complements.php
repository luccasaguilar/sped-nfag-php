<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag;

use DOMDocument;
use DOMXPath;
use Lna\Sped\Nfag\Common\XmlException;

final class Complements
{
    public static function toAuthorize(string $signedNfagXml, string $responseXml): string
    {
        $domResp = new DOMDocument('1.0','UTF-8'); $domResp->loadXML($responseXml); $xpath = new DOMXPath($domResp);
        $prot = $xpath->query('//*[local-name()="protNFAg"]')->item(0); if(!$prot) throw new XmlException('Authorization protocol protNFAg not found in response.');
        $domNfag = new DOMDocument('1.0','UTF-8'); $domNfag->loadXML($signedNfagXml);
        $proc = new DOMDocument('1.0','UTF-8'); $root=$proc->createElement('nfagProc'); $root->setAttribute('xmlns','http://www.portalfiscal.inf.br/nfag'); $root->setAttribute('versao','1.00');
        $root->appendChild($proc->importNode($domNfag->documentElement, true)); $root->appendChild($proc->importNode($prot, true)); $proc->appendChild($root); return (string)$proc->saveXML($proc->documentElement);
    }
}
