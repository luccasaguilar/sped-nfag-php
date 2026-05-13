<?php
require __DIR__ . '/../vendor/autoload.php';
use Lna\Sped\Nfag\Common\Config;
use Lna\Sped\Nfag\Tools;
$config = Config::fromJsonFile(__DIR__ . '/config.json');
$tools = new Tools($config);
$xml = file_get_contents(__DIR__ . '/xml/nfag-sample.xml');
$signed = $tools->signNFAg($xml);
$errors = $tools->validateNFAg($signed);
if ($errors !== []) { echo "XSD errors:\n".implode(PHP_EOL, $errors); exit(1); }
echo $tools->sefazRecepcao($signed);
