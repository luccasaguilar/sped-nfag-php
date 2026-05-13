<?php
require __DIR__.'/../vendor/autoload.php';
use Lna\Sped\Nfag\Common\Config;
use Lna\Sped\Nfag\Tools;
$config=Config::fromJsonFile(__DIR__.'/config.json');
$tools=new Tools($config);
echo $tools->sefazStatusServico();
