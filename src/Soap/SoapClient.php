<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Soap;

use Lna\Sped\Nfag\Certificate\Certificate;
use Lna\Sped\Nfag\Common\Config;
use Lna\Sped\Nfag\Common\SoapException;

final class SoapClient
{
    public function __construct(private readonly Config $config, private readonly Certificate $certificate) {}
    public function send(string $service, string $method, string $xml): string
    {
        $url = WebserviceMap::endpoint($this->config, $service);
        $pem = $this->certificate->saveTempPemBundle();
        try {
            $ch = curl_init($url); if ($ch === false) throw new SoapException('Unable to initialize cURL.');
            $envelope = SoapEnvelope::wrap($xml, $method);
            curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$envelope, CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>$this->config->soapTimeout, CURLOPT_CONNECTTIMEOUT=>10, CURLOPT_SSLCERT=>$pem, CURLOPT_SSLKEY=>$pem, CURLOPT_HTTPHEADER=>['Content-Type: application/soap+xml; charset=utf-8','Content-Length: '.strlen($envelope)]]);
            $response = curl_exec($ch);
            if ($response === false) throw new SoapException('SOAP request failed: '.curl_error($ch));
            $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            if ($status >= 400) throw new SoapException("SOAP HTTP error {$status}: {$response}");
            return (string)$response;
        } finally { if (isset($ch) && $ch) curl_close($ch); if (is_file($pem)) @unlink($pem); }
    }
}
