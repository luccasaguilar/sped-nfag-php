# Notas técnicas

## Modelo de processamento da NFAg

O serviço de recepção da NFAg é projetado como autorização síncrona; portanto a biblioteca expõe `sefazRecepcao()` direto, sem `nRec` / polling.

## Conformidade com os XSDs v1.00

A API do `Make` segue a ordem do `<xs:sequence>` de cada `complexType`. Pontos importantes:

- **`infNFAg/Id`**: padrão XSD é `NFAG[0-9]{6}[A-Z0-9]{12}[0-9]{26}` (prefixo em **maiúsculas**). O builder aceita "NFAg" como entrada por compatibilidade e converte para "NFAG" automaticamente.
- **Endereços**: `TEndeEmi` (usado em `enderEmit`/`enderDest`) é `xLgr, nro, xCpl?, xBairro, cMun, xMun, CEP, UF, fone?, email?`. Não há `cPais`/`xPais`. O `enderCorresp` usa `TEndereco` que inclui `cPais?` e `xPais?` e CEP opcional.
- **Latitude/Longitude**: requerem exatamente 6 casas decimais (`-30.033100`, não `-30.0331`).
- **`detEvento`** (cancelamento e demais eventos) envelopa o conteúdo num único elemento. Para cancelamento o wrapper é `<evCancNFAg>`. O `MakeEvent::tagCancelamento` já cuida disso; para outros eventos use `tagEventoGenerico()` passando o wrapper correto.
- **`total`** exige `vProd, vRetTribTot, vCOFINS, vPIS, vTFS, vTFU, vNF, IBSCBSTot, vTotDFe` (todos obrigatórios). Use `tagvRetTribTot()` e `tagIBSCBSTot()` para preencher os grupos compostos.
- **`gAgencia`** exige `gHistCons` (1..5) com `gCons` (1..13). Use `taggHistCons()` + `taggCons()`.
- **`IBSCBSTot`** é um grupo grande e aninhado (`vBCIBSCBS`, `gIBS{gIBSUF,gIBSMun}`, `gCBS`). Passe como array associativo para `tagIBSCBSTot()` — a árvore é montada por `buildSubtree`.

## Assinatura digital

`Signer` usa XMLDSig enveloped + SHA1 + C14N. A `Signature` é REQUERIDA pelos XSDs `NFAg` e `eventoNFAg`; logo, validação XSD do documento só passa após `signNFAg()`/`signEvent()`.

## Validação XSD

Use `Tools::validateAuto($xml)` para que o `Validator` descubra o XSD pelo elemento raiz (`NFAg → nfag_v1.00.xsd`, `eventoNFAg → eventoNFAg_v1.00.xsd`, etc.). O mapa está em `Validator::SCHEMA_MAP`.

## Webservices SOAP

Endpoints em `Soap/WebserviceMap.php` apontam para `nfag.svrs.rs.gov.br` (homologação e produção). Confirme contra o MOC e WSDLs atualizados — o nome do método e o namespace `http://www.portalfiscal.inf.br/nfag/wsdl/{Servico}` podem mudar entre minutas.

## Aviso

Este projeto não é oficial; valide sempre contra o MOC, os XSDs versionados e os WSDLs em produção/homologação do SVRS antes de operar.
