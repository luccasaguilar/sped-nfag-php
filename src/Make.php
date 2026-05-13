<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;

final class Make
{
    private DOMDocument $dom; private DOMElement $nfag; private ?DOMElement $infNFAg = null;
    public function __construct(private readonly string $version = '1.00')
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8'); $this->dom->formatOutput=false; $this->dom->preserveWhiteSpace=false;
        $this->nfag = $this->dom->createElement('NFAg'); $this->nfag->setAttribute('xmlns','http://www.portalfiscal.inf.br/nfag'); $this->dom->appendChild($this->nfag);
    }
    public function infNFAg(string $id, array $children=[]): self { if (!str_starts_with($id,'NFAg')) throw new InvalidArgumentException('NFAg Id must start with "NFAg".'); $this->infNFAg=$this->dom->createElement('infNFAg'); $this->infNFAg->setAttribute('Id',$id); $this->infNFAg->setAttribute('versao',$this->version); foreach($children as $n=>$v) $this->appendValue($this->infNFAg,(string)$n,$v); $this->nfag->appendChild($this->infNFAg); return $this; }
    public function ide(array $data): self { return $this->appendGroup('ide',$data); }
    public function emit(array $data): self { return $this->appendGroup('emit',$data); }
    public function dest(array $data): self { return $this->appendGroup('dest',$data); }
    public function assinante(array $data): self { return $this->appendGroup('assinante',$data); }
    public function det(int $nItem, array $data): self { $det=$this->dom->createElement('det'); $det->setAttribute('nItem',(string)$nItem); foreach($data as $n=>$v) $this->appendValue($det,(string)$n,$v); $this->requireInfNFAg()->appendChild($det); return $this; }
    public function total(array $data): self { return $this->appendGroup('total',$data); }
    public function pag(array $data): self { return $this->appendGroup('pag',$data); }
    public function infAdic(array $data): self { return $this->appendGroup('infAdic',$data); }
    public function toXml(): string { return (string)$this->dom->saveXML($this->dom->documentElement); }
    private function appendGroup(string $name, array $data): self { $g=$this->dom->createElement($name); foreach($data as $n=>$v) $this->appendValue($g,(string)$n,$v); $this->requireInfNFAg()->appendChild($g); return $this; }
    private function appendValue(DOMElement $parent, string $name, mixed $value): void { if (is_array($value)) { if ($this->isList($value)) { foreach($value as $item) { $node=$this->dom->createElement($name); foreach($item as $cn=>$cv) $this->appendValue($node,(string)$cn,$cv); $parent->appendChild($node); } return; } $node=$this->dom->createElement($name); foreach($value as $cn=>$cv) $this->appendValue($node,(string)$cn,$cv); $parent->appendChild($node); return; } if ($value===null) return; $parent->appendChild($this->dom->createElement($name, htmlspecialchars((string)$value, ENT_XML1))); }
    private function requireInfNFAg(): DOMElement { if (!$this->infNFAg) throw new InvalidArgumentException('Call infNFAg() before adding groups.'); return $this->infNFAg; }
    private function isList(array $array): bool { return array_keys($array) === range(0, count($array)-1); }
}
