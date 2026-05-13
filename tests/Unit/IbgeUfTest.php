<?php

declare(strict_types=1);
namespace Lna\Sped\Nfag\Tests\Unit;
use Lna\Sped\Nfag\IbgeUf;
use PHPUnit\Framework\TestCase;
final class IbgeUfTest extends TestCase { public function test_returns_ibge_code(): void { $this->assertSame('43', IbgeUf::code('RS')); $this->assertSame('35', IbgeUf::code('sp')); } }
