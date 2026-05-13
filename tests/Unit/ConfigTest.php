<?php

declare(strict_types=1);
namespace Lna\Sped\Nfag\Tests\Unit;
use InvalidArgumentException;
use Lna\Sped\Nfag\Common\Config;
use PHPUnit\Framework\TestCase;
final class ConfigTest extends TestCase
{
    public function test_accepts_valid_config(): void { $c = new Config(2,'RS','12345678000199','/tmp/cert.pfx','secret','schemes'); $this->assertFalse($c->isProduction()); }
    public function test_rejects_invalid_environment(): void { $this->expectException(InvalidArgumentException::class); new Config(3,'RS','12345678000199','/tmp/cert.pfx','secret','schemes'); }
}
