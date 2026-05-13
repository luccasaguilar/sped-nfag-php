<?php

require __DIR__ . '/../vendor/autoload.php';

use Lna\Sped\Nfag\Make;

$make = new Make('1.00');

// Chave (44 dígitos): cUF(2) + AAMM(4) + CNPJ(14) + mod(2) + serie(3) + nNF(9) + tpEmis(1) + cNF(7) + cDV(1) + filler
$chave = str_pad('43260100000000000000750010000000011000000100', 44, '0', STR_PAD_RIGHT);

$make->taginfNFAg((object) ['Id' => 'NFAG' . $chave]);

$make->tagide((object) [
    'cUF'          => '43',
    'tpAmb'        => '2',
    'mod'          => '75',
    'serie'        => '1',
    'nNF'          => '1',
    'cNF'          => '0000001',
    'cDV'          => '0',
    'dhEmi'        => date('c'),
    'tpEmis'       => '1',
    'nSiteAutoriz' => '1',
    'cMunFG'       => '4314902',
    'finNFAg'      => '0',
    'tpFat'        => '1',
    'verProc'      => 'sped-nfag-php/1.0',
]);

$make->tagemit((object) [
    'CNPJ'  => '00000000000000',
    'IE'    => '1234567890',
    'xNome' => 'Companhia de Agua Homologacao',
    'xFant' => 'CA Homologacao',
]);
$make->tagenderEmit((object) [
    'xLgr'    => 'Av. Ipiranga',
    'nro'     => '1000',
    'xBairro' => 'Praia de Belas',
    'cMun'    => '4314902',
    'xMun'    => 'Porto Alegre',
    'UF'      => 'RS',
    'CEP'     => '90160091',
]);

$make->tagdest((object) [
    'xNome' => 'Consumidor de Homologacao',
    'CPF'   => '00000000000',
]);
$make->tagenderDest((object) [
    'xLgr'    => 'Rua Exemplo',
    'nro'     => '50',
    'xBairro' => 'Centro',
    'cMun'    => '4314902',
    'xMun'    => 'Porto Alegre',
    'UF'      => 'RS',
    'CEP'     => '90000000',
]);

$make->tagligacao((object) [
    'idLigacao'  => 'LIG000000001',
    'tpLigacao'  => '3',
    'latGPS'     => '-30.033100',
    'longGPS'    => '-51.230000',
]);

$make->tagdet((object) ['nItem' => 1]);
$make->tagprod((object) [
    'item'         => 1,
    'indOrigemQtd' => '2',
    'cProd'        => '001',
    'xProd'        => 'Abastecimento de agua',
    'cClass'       => '0000001',
    'uMed'         => '1',
    'qFaturada'    => '10.0000',
    'vItem'        => '10.00',
    'vProd'        => '100.00',
]);
$make->tagimposto((object) ['item' => 1]);
$make->tagIBSCBS((object) [
    'item'       => 1,
    'CST'        => '000',
    'cClassTrib' => '000001',
]);

$make->tagtotal((object) [
    'vProd'   => '100.00',
    'vNF'     => '100.00',
    'vTotDFe' => '100.00',
]);

$make->taggAgencia((object) [
    'nAgenciaAtend'     => 'Central de Atendimento',
    'enderAgenciaAtend' => 'Av. Ipiranga, 1000 - Porto Alegre/RS',
]);

$make->taginfNFAgSupl((object) [
    'qrCodNFAg' => 'https://www.svrs.rs.gov.br/nfag/qrcode?chNFAg=' . $chave . '&tpAmb=2',
]);

$xml = $make->monta();

@mkdir(__DIR__ . '/xml', 0755, true);
file_put_contents(__DIR__ . '/xml/nfag-sample.xml', $xml);
echo $xml . PHP_EOL;
