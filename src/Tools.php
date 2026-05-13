<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag;

use Lna\Sped\Nfag\Certificate\Certificate;
use Lna\Sped\Nfag\Common\Config;
use Lna\Sped\Nfag\Soap\SoapClient;
use Lna\Sped\Nfag\Xml\Signer;
use Lna\Sped\Nfag\Xml\Validator;

final class Tools
{
    private Certificate $certificate; private SoapClient $soap;
    public function __construct(private readonly Config $config) { $this->certificate = Certificate::fromPfxFile($config->certPfx, $config->certPassword); $this->soap = new SoapClient($config, $this->certificate); }
    public function signNFAg(string $xml): string { return (new Signer())->sign($xml, $this->certificate, 'infNFAg', 'Id'); }
    public function signEvent(string $xml): string { return (new Signer())->sign($xml, $this->certificate, 'infEvento', 'Id'); }
    public function validateNFAg(string $xml, string $xsdFile = 'nfag_v1.00.xsd'): array { return (new Validator())->validate($xml, rtrim($this->config->schemesPath, '/\\').DIRECTORY_SEPARATOR.$xsdFile); }
    public function sefazRecepcao(string $signedNfagXml): string { return $this->soap->send('NFAgRecepcao', 'NFAgRecepcao', $signedNfagXml); }
    public function sefazConsulta(string $chNFAg): string { $xml='<consSitNFAg xmlns="http://www.portalfiscal.inf.br/nfag" versao="'.$this->config->version.'"><tpAmb>'.$this->config->tpAmb.'</tpAmb><xServ>CONSULTAR</xServ><chNFAg>'.$chNFAg.'</chNFAg></consSitNFAg>'; return $this->soap->send('NFAgConsulta','NFAgConsulta',$xml); }
    public function sefazStatusServico(): string { $xml='<consStatServNFAg xmlns="http://www.portalfiscal.inf.br/nfag" versao="'.$this->config->version.'"><tpAmb>'.$this->config->tpAmb.'</tpAmb><cUF>'.IbgeUf::code($this->config->siglaUF).'</cUF><xServ>STATUS</xServ></consStatServNFAg>'; return $this->soap->send('NFAgStatusServico','NFAgStatusServico',$xml); }
    public function sefazRecepcaoEvento(string $signedEventXml): string { return $this->soap->send('NFAgRecepcaoEvento','NFAgRecepcaoEvento',$signedEventXml); }
}
