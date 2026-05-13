# sped-nfag-php

Biblioteca PHP experimental para emissão da **NFAg - Nota Fiscal de Água e Saneamento Eletrônica, modelo 75**, inspirada na organização de bibliotecas como `nfephp-org/sped-nfe`.

> Status: experimental. A documentação da NFAg ainda foi publicada como minuta no portal SVRS. Use este projeto como base técnica e valide sempre contra o MOC, schemas XSD oficiais e WSDLs atuais.

## Recursos

- Montagem básica do XML da NFAg
- Assinatura XML Digital enveloped com certificado A1/PFX
- Validação por XSD local
- SOAP client para os serviços oficiais SVRS
- Consulta de status do serviço
- Consulta de situação da NFAg
- Recepção síncrona da NFAg
- Recepção de evento
- Complemento de autorização (`nfagProc`)
- Configuração por JSON
- Exemplos prontos
- Testes PHPUnit

## Serviços SVRS mapeados

Produção e homologação:

- `NFAgRecepcao`
- `NFAgRecepcaoEvento`
- `NFAgConsulta`
- `NFAgStatusServico`

O WSDL pode ser obtido adicionando `?wsdl` ao endpoint.

## Instalação

```bash
composer install
```

## Configuração

```bash
cp examples/config.example.json examples/config.json
```

## Testes

```bash
vendor/bin/phpunit
```

## Aviso

Este projeto não é oficial, não substitui o MOC e não garante autorização em produção sem validação dos schemas, WSDLs, regras de validação e credenciamento do contribuinte.
