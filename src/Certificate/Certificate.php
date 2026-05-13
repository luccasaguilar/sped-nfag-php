<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Certificate;

use InvalidArgumentException;
use RuntimeException;

final class Certificate
{
    private function __construct(private string $privateKeyPem, private string $publicCertPem) {}

    public static function fromPfxFile(string $path, string $password): self
    {
        if (! is_file($path)) throw new InvalidArgumentException("PFX file not found: {$path}");
        $certs = [];
        if (! openssl_pkcs12_read((string) file_get_contents($path), $certs, $password)) {
            throw new RuntimeException('Unable to read PFX certificate.');
        }
        if (empty($certs['pkey']) || empty($certs['cert'])) {
            throw new RuntimeException('PFX does not contain private key and public certificate.');
        }
        return new self($certs['pkey'], $certs['cert']);
    }
    public function privateKeyPem(): string { return $this->privateKeyPem; }
    public function publicCertPem(): string { return $this->publicCertPem; }
    public function publicCertClean(): string { return trim(str_replace(['-----BEGIN CERTIFICATE-----','-----END CERTIFICATE-----',"\r","\n"], '', $this->publicCertPem)); }
    public function saveTempPemBundle(): string { $path = tempnam(sys_get_temp_dir(), 'nfag-cert-'); file_put_contents($path, $this->privateKeyPem.PHP_EOL.$this->publicCertPem); return $path; }
}
