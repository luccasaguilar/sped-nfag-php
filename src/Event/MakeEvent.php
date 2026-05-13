<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Event;

use DOMDocument;

final class MakeEvent
{
    public function __construct(private readonly string $version = '1.00') {}
    public function cancellation(int $tpAmb, string $cOrgao, string $cnpj, string $chNFAg, int $nSeqEvento, string $xJust, string $dhEvento, string $tpEvento='110111'): string
    {
        $id='ID'.$tpEvento.$chNFAg.str_pad((string)$nSeqEvento,2,'0',STR_PAD_LEFT); $dom=new DOMDocument('1.0','UTF-8');
        $evento=$dom->createElement('eventoNFAg'); $evento->setAttribute('xmlns','http://www.portalfiscal.inf.br/nfag'); $evento->setAttribute('versao',$this->version);
        $inf=$dom->createElement('infEvento'); $inf->setAttribute('Id',$id);
        foreach(['cOrgao'=>$cOrgao,'tpAmb'=>$tpAmb,'CNPJ'=>$cnpj,'chNFAg'=>$chNFAg,'dhEvento'=>$dhEvento,'tpEvento'=>$tpEvento,'nSeqEvento'=>$nSeqEvento,'verEvento'=>$this->version] as $n=>$v) $inf->appendChild($dom->createElement($n,(string)$v));
        $det=$dom->createElement('detEvento'); $det->setAttribute('versao',$this->version); $det->appendChild($dom->createElement('descEvento','Cancelamento')); $det->appendChild($dom->createElement('xJust',$xJust)); $inf->appendChild($det); $evento->appendChild($inf); $dom->appendChild($evento); return (string)$dom->saveXML($dom->documentElement);
    }
}
