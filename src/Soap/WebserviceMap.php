<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Soap;

use InvalidArgumentException;
use Lna\Sped\Nfag\Common\Config;

final class WebserviceMap
{
    private const PRODUCTION = [
        'NFAgRecepcao' => 'https://nfag.svrs.rs.gov.br/WS/NFAgRecepcao/NFAgRecepcao.asmx',
        'NFAgRecepcaoEvento' => 'https://nfag.svrs.rs.gov.br/WS/NFAgRecepcaoEvento/NFAgRecepcaoEvento.asmx',
        'NFAgConsulta' => 'https://nfag.svrs.rs.gov.br/WS/NFAgConsulta/NFAgConsulta.asmx',
        'NFAgStatusServico' => 'https://nfag.svrs.rs.gov.br/WS/NFAgStatusServico/NFAgStatusServico.asmx',
    ];
    private const HOMOLOGATION = [
        'NFAgRecepcao' => 'https://nfag-homologacao.svrs.rs.gov.br/WS/NFAgRecepcao/NFAgRecepcao.asmx',
        'NFAgRecepcaoEvento' => 'https://nfag-homologacao.svrs.rs.gov.br/WS/NFAgRecepcaoEvento/NFAgRecepcaoEvento.asmx',
        'NFAgConsulta' => 'https://nfag-homologacao.svrs.rs.gov.br/WS/NFAgConsulta/NFAgConsulta.asmx',
        'NFAgStatusServico' => 'https://nfag-homologacao.svrs.rs.gov.br/WS/NFAgStatusServico/NFAgStatusServico.asmx',
    ];
    public static function endpoint(Config $config, string $service): string { $map = $config->isProduction() ? self::PRODUCTION : self::HOMOLOGATION; if (!isset($map[$service])) throw new InvalidArgumentException("Unknown NFAg service: {$service}"); return $map[$service]; }
    public static function wsdl(Config $config, string $service): string { return self::endpoint($config, $service).'?wsdl'; }
}
