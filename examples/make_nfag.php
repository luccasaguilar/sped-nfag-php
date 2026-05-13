<?php
require __DIR__ . '/../vendor/autoload.php';
use Lna\Sped\Nfag\Make;
$xml = (new Make('1.00'))
    ->infNFAg('NFAg43000000000000000000000000000000000000000000')
    ->ide(['cUF'=>'43','tpAmb'=>'2','mod'=>'75','serie'=>'1','nNF'=>'1','cNF'=>'00000001','dhEmi'=>date('c'),'tpEmis'=>'1','tpFinNF'=>'0'])
    ->emit(['CNPJ'=>'00000000000000','xNome'=>'Companhia de Agua Homologacao'])
    ->dest(['CPF'=>'00000000000','xNome'=>'Consumidor de Homologacao'])
    ->assinante(['idLigacao'=>'LIG000000001','tpAssinante'=>'1'])
    ->det(1, ['prod'=>['cProd'=>'001','xProd'=>'Abastecimento de agua','cClass'=>'000001','qFaturada'=>'10.0000','vItem'=>'100.00'],'imposto'=>['IBSCBS'=>['CST'=>'000','cClassTrib'=>'000001']]])
    ->total(['vProd'=>'100.00','vTotDFe'=>'100.00'])
    ->toXml();
file_put_contents(__DIR__.'/xml/nfag-sample.xml', $xml);
echo $xml.PHP_EOL;
