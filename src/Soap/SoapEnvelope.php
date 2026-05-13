<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Soap;

/**
 * Envelopa o XML conforme padrão SOAP 1.2 dos webservices NFAg/SVRS.
 *
 * Estrutura:
 *   <soap12:Envelope ...>
 *     <soap12:Body>
 *       <{Servico} xmlns="http://www.portalfiscal.inf.br/nfag/wsdl/{Servico}">
 *         <nfagDadosMsg>{XML do documento}</nfagDadosMsg>
 *       </{Servico}>
 *     </soap12:Body>
 *   </soap12:Envelope>
 *
 * O nome do elemento e namespace devem coincidir com o WSDL do serviço.
 */
final class SoapEnvelope
{
    public const SERVICE_NS_BASE = 'http://www.portalfiscal.inf.br/nfag/wsdl/';

    public static function wrap(string $bodyXml, string $method): string
    {
        $bodyXml = self::stripXmlDeclaration($bodyXml);
        $ns = self::SERVICE_NS_BASE . $method;
        return '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap12:Envelope'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            . ' xmlns:xsd="http://www.w3.org/2001/XMLSchema"'
            . ' xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">'
            . '<soap12:Body>'
            . '<' . $method . ' xmlns="' . $ns . '">'
            . '<nfagDadosMsg>' . $bodyXml . '</nfagDadosMsg>'
            . '</' . $method . '>'
            . '</soap12:Body>'
            . '</soap12:Envelope>';
    }

    private static function stripXmlDeclaration(string $xml): string
    {
        return (string) preg_replace('/^<\?xml[^?]*\?>/', '', ltrim($xml));
    }
}
