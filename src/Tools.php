<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag;

use Lna\Sped\Nfag\Certificate\Certificate;
use Lna\Sped\Nfag\Common\Config;
use Lna\Sped\Nfag\Soap\SoapClient;
use Lna\Sped\Nfag\Xml\Signer;
use Lna\Sped\Nfag\Xml\Validator;

/**
 * Camada de orquestração: assina, valida e dispara SOAP.
 *
 * Webservices SVRS (Recepcao, RecepcaoEvento, Consulta, StatusServico)
 * conforme MOC NFAg v1.00. URLs reais devem ser confirmadas no portal SVRS;
 * mapeamento atual em Soap/WebserviceMap.php.
 */
final class Tools
{
    public const SERVICES = [
        'recepcao'      => 'NFAgRecepcao',
        'evento'        => 'NFAgRecepcaoEvento',
        'consulta'      => 'NFAgConsulta',
        'status'        => 'NFAgStatusServico',
    ];

    private Certificate $certificate;
    private SoapClient $soap;

    public function __construct(private readonly Config $config)
    {
        $this->certificate = Certificate::fromPfxFile($config->certPfx, $config->certPassword);
        $this->soap = new SoapClient($config, $this->certificate);
    }

    public function certificate(): Certificate
    {
        return $this->certificate;
    }

    /* ===================== Assinatura ===================== */

    public function signNFAg(string $xml): string
    {
        return (new Signer())->sign($xml, $this->certificate, 'infNFAg', 'Id');
    }

    public function signEvent(string $xml): string
    {
        return (new Signer())->sign($xml, $this->certificate, 'infEvento', 'Id');
    }

    /* ===================== Validação XSD ===================== */

    /**
     * Valida usando XSD explícito (compatível com versão anterior).
     */
    public function validateNFAg(string $xml, string $xsdFile = 'nfag_v1.00.xsd'): array
    {
        return (new Validator())->validate(
            $xml,
            rtrim($this->config->schemesPath, '/\\') . DIRECTORY_SEPARATOR . $xsdFile
        );
    }

    /**
     * Detecta o XSD pelo elemento raiz e valida.
     */
    public function validateAuto(string $xml): array
    {
        return (new Validator())->validateAuto($xml, $this->config->schemesPath);
    }

    /* ===================== Webservices ===================== */

    public function sefazRecepcao(string $signedNfagXml): string
    {
        return $this->soap->send(self::SERVICES['recepcao'], 'NFAgRecepcao', $signedNfagXml);
    }

    public function sefazRecepcaoEvento(string $signedEventXml): string
    {
        return $this->soap->send(self::SERVICES['evento'], 'NFAgRecepcaoEvento', $signedEventXml);
    }

    public function sefazConsulta(string $chNFAg): string
    {
        if (! preg_match('/^[0-9]{44}$/', $chNFAg)) {
            throw new \InvalidArgumentException('chNFAg deve ter 44 dígitos.');
        }
        $xml = '<consSitNFAg xmlns="' . Make::NS . '" versao="' . $this->config->version . '">'
            . '<tpAmb>' . $this->config->tpAmb . '</tpAmb>'
            . '<xServ>CONSULTAR</xServ>'
            . '<chNFAg>' . $chNFAg . '</chNFAg>'
            . '</consSitNFAg>';
        return $this->soap->send(self::SERVICES['consulta'], 'NFAgConsulta', $xml);
    }

    public function sefazStatus(): string
    {
        $xml = '<consStatServNFAg xmlns="' . Make::NS . '" versao="' . $this->config->version . '">'
            . '<tpAmb>' . $this->config->tpAmb . '</tpAmb>'
            . '<cUF>' . IbgeUf::code($this->config->siglaUF) . '</cUF>'
            . '<xServ>STATUS</xServ>'
            . '</consStatServNFAg>';
        return $this->soap->send(self::SERVICES['status'], 'NFAgStatusServico', $xml);
    }

    /** Alias retrocompatível. */
    public function sefazStatusServico(): string
    {
        return $this->sefazStatus();
    }
}
