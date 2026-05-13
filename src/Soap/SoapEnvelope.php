<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Soap;

final class SoapEnvelope
{
    public static function wrap(string $bodyXml, string $method): string
    {
        return '<?xml version="1.0" encoding="utf-8"?>'
            . '<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">'
            . '<soap12:Body>'
            . '<'.$method.' xmlns="http://www.portalfiscal.inf.br/nfag/wsdl/'.$method.'">'
            . '<nfagDadosMsg>'.$bodyXml.'</nfagDadosMsg>'
            . '</'.$method.'>'
            . '</soap12:Body></soap12:Envelope>';
    }
}
