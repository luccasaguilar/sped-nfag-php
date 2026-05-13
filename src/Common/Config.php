<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Common;

use InvalidArgumentException;

final class Config
{
    public function __construct(
        public readonly int $tpAmb,
        public readonly string $siglaUF,
        public readonly string $cnpj,
        public readonly string $certPfx,
        public readonly string $certPassword,
        public readonly string $schemesPath,
        public readonly int $soapTimeout = 30,
        public readonly string $version = '1.00',
    ) {
        if (! in_array($this->tpAmb, [1, 2], true)) {
            throw new InvalidArgumentException('tpAmb must be 1 for production or 2 for homologation.');
        }
        if (! preg_match('/^\d{14}$/', $this->cnpj)) {
            throw new InvalidArgumentException('cnpj must contain exactly 14 digits.');
        }
    }

    public static function fromJsonFile(string $path): self
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("Config file not found: {$path}");
        }
        $data = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        return new self(
            tpAmb: (int) ($data['tpAmb'] ?? 2),
            siglaUF: (string) ($data['siglaUF'] ?? 'RS'),
            cnpj: preg_replace('/\D/', '', (string) ($data['cnpj'] ?? '')),
            certPfx: (string) ($data['certPfx'] ?? ''),
            certPassword: (string) ($data['certPassword'] ?? ''),
            schemesPath: (string) ($data['schemesPath'] ?? 'schemes/PRNFAG'),
            soapTimeout: (int) ($data['soapTimeout'] ?? 30),
            version: (string) ($data['version'] ?? '1.00'),
        );
    }
    public function isProduction(): bool { return $this->tpAmb === 1; }
}
