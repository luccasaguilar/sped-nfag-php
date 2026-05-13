<?php

require __DIR__ . '/../vendor/autoload.php';

use Lna\Sped\Nfag\Common\Config;
use Lna\Sped\Nfag\Complements;
use Lna\Sped\Nfag\Tools;

$config = Config::fromJsonFile(__DIR__ . '/config.json');
$tools  = new Tools($config);

$xml = file_get_contents(__DIR__ . '/xml/nfag-sample.xml');
$signed = $tools->signNFAg($xml);

// Valida automaticamente (descobre o XSD pelo elemento raiz)
$errors = $tools->validateAuto($signed);
if ($errors !== []) {
    fwrite(STDERR, "Erros XSD:\n" . implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

$response = $tools->sefazRecepcao($signed);
echo "Resposta SEFAZ:\n" . $response . PHP_EOL;

// Se autorizado, monta o nfagProc
try {
    $proc = Complements::toAuthorize($signed, $response);
    file_put_contents(__DIR__ . '/xml/nfag-proc.xml', $proc);
    echo "nfagProc gerado em examples/xml/nfag-proc.xml\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Não foi possível gerar nfagProc: " . $e->getMessage() . PHP_EOL);
}
