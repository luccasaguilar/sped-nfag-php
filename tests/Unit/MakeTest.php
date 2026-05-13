<?php

declare(strict_types=1);
namespace Lna\Sped\Nfag\Tests\Unit;
use Lna\Sped\Nfag\Make;
use PHPUnit\Framework\TestCase;
final class MakeTest extends TestCase
{
    public function test_can_build_basic_nfag_xml(): void { $xml=(new Make())->infNFAg('NFAg43000000000000000000000000000000000000000000')->ide(['cUF'=>'43','tpAmb'=>'2','mod'=>'75'])->emit(['CNPJ'=>'12345678000199','xNome'=>'Emitente'])->dest(['CPF'=>'00000000000','xNome'=>'Destinatario'])->total(['vTotDFe'=>'100.00'])->toXml(); $this->assertStringContainsString('<NFAg', $xml); $this->assertStringContainsString('<infNFAg', $xml); $this->assertStringContainsString('<mod>75</mod>', $xml); }
}
