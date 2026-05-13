<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Tests\Unit;

use Lna\Sped\Nfag\Make;
use Lna\Sped\Nfag\Tests\Support\SampleBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MakeTest extends TestCase
{
    public function test_falha_se_montar_sem_infNFAg(): void
    {
        $this->expectException(\RuntimeException::class);
        (new Make())->monta();
    }

    public function test_monta_xml_com_estrutura_minima(): void
    {
        $make = SampleBuilder::minimal();
        $xml = $make->monta();
        $this->assertStringContainsString('<NFAg', $xml);
        $this->assertStringContainsString('<infNFAg Id="NFAG', $xml);
        $this->assertStringContainsString('<mod>75</mod>', $xml);
        $this->assertStringContainsString('<ligacao>', $xml);
        $this->assertStringContainsString('<det nItem="1">', $xml);
        $this->assertStringContainsString('<total>', $xml);
        $this->assertStringContainsString('<gAgencia>', $xml);
        $this->assertStringContainsString('<infNFAgSupl>', $xml);
    }

    public function test_ordem_dos_blocos_segue_xsd(): void
    {
        $xml = SampleBuilder::minimal()->monta();
        $posIde     = strpos($xml, '<ide>');
        $posEmit    = strpos($xml, '<emit>');
        $posDest    = strpos($xml, '<dest>');
        $posLig     = strpos($xml, '<ligacao>');
        $posDet     = strpos($xml, '<det ');
        $posTotal   = strpos($xml, '<total>');
        $posAg      = strpos($xml, '<gAgencia>');
        $posSupl    = strpos($xml, '<infNFAgSupl>');
        $this->assertTrue($posIde < $posEmit, 'ide deve preceder emit');
        $this->assertTrue($posEmit < $posDest, 'emit deve preceder dest');
        $this->assertTrue($posDest < $posLig, 'dest deve preceder ligacao');
        $this->assertTrue($posLig < $posDet, 'ligacao deve preceder det');
        $this->assertTrue($posDet < $posTotal, 'det deve preceder total');
        $this->assertTrue($posTotal < $posAg, 'total deve preceder gAgencia');
        $this->assertTrue($posAg < $posSupl, 'gAgencia deve preceder infNFAgSupl');
    }

    public function test_destinatario_aceita_cpf_cnpj_ou_idOutros(): void
    {
        $make = SampleBuilder::minimal(destOverride: (object) [
            'xNome' => 'Cliente CNPJ',
            'CNPJ'  => '12345678000199',
        ]);
        $xml = $make->monta();
        $this->assertStringContainsString('<CNPJ>12345678000199</CNPJ>', $xml);
    }

    public function test_det_com_multiplos_itens(): void
    {
        $make = SampleBuilder::minimal();
        // adiciona um segundo item
        $make->tagdet((object) ['nItem' => 2]);
        $make->tagprod((object) [
            'item'         => 2,
            'indOrigemQtd' => '2',
            'cProd'        => '002',
            'xProd'        => 'Esgoto',
            'cClass'       => '0000002',
            'uMed'         => '1',
            'qFaturada'    => '5.0000',
            'vItem'        => '50.00',
            'vProd'        => '50.00',
        ]);
        $make->tagimposto((object) ['item' => 2]);
        $make->tagIBSCBS((object) ['item' => 2, 'CST' => '000', 'cClassTrib' => '000001']);
        $xml = $make->monta();
        $this->assertStringContainsString('<det nItem="1">', $xml);
        $this->assertStringContainsString('<det nItem="2">', $xml);
    }

    public function test_xml_eh_well_formed(): void
    {
        $xml = SampleBuilder::minimal()->monta();
        $dom = new \DOMDocument();
        $this->assertTrue($dom->loadXML($xml), 'XML gerado deve ser well-formed');
    }

    public function test_acumula_erros_de_campos_obrigatorios_ausentes(): void
    {
        $make = new Make();
        $make->taginfNFAg((object) ['Id' => 'NFAG' . str_repeat('0', 44)]);
        // ide sem campos obrigatórios
        $make->tagide(new stdClass());
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Campo obrigat/u');
        $make->monta();
    }
}
