<?php
require __DIR__ . '/../vendor/autoload.php';
use Lna\Sped\Nfag\Common\Config;
use Lna\Sped\Nfag\Tools;
$config = Config::fromJsonFile(__DIR__ . '/config.json');
$tools = new Tools($config);
$chNFAg = $argv[1] ?? null;
if (!$chNFAg) { fwrite(STDERR, "Usage: php consult.php CHAVE_NFAG\n"); exit(1); }
echo $tools->sefazConsulta($chNFAg);
