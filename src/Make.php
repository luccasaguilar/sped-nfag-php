<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use RuntimeException;
use stdClass;

/**
 * Builder do XML da NFAg (Nota Fiscal de Água e Saneamento, modelo 75)
 * conforme schemas XSD v1.00 (nfag_v1.00.xsd, nfagTiposBasico_v1.00.xsd,
 * DFeTiposBasicos_v1.00.xsd, tiposGeralNFAg_v1.00.xsd).
 *
 * API inspirada em nfephp-org/sped-nfe (tagXxx + stdClass), respeitando
 * ordem do <xs:sequence/> de cada complexType.
 */
final class Make
{
    public const NS = 'http://www.portalfiscal.inf.br/nfag';

    private DOMDocument $dom;
    private string $version;
    private array $errors = [];
    private string $chNFAg = '';

    private DOMElement $NFAg;
    private ?DOMElement $infNFAg = null;

    private ?DOMElement $ide = null;
    private ?DOMElement $gCompraGov = null;

    private ?DOMElement $emit = null;
    private ?DOMElement $enderEmit = null;

    private ?DOMElement $dest = null;
    private ?DOMElement $enderDest = null;

    private ?DOMElement $ligacao = null;
    private ?DOMElement $gSub = null;

    /** @var DOMElement[] */
    private array $aGMed = [];

    private ?DOMElement $gFatConjunto = null;

    /** @var DOMElement[] */
    private array $aDet = [];
    /** @var array<int, DOMElement[]> */
    private array $aGTarif = [];
    /** @var DOMElement[] */
    private array $aProd = [];
    /** @var DOMElement[] */
    private array $aGMedicao = [];
    /** @var DOMElement[] */
    private array $aImposto = [];
    /** @var DOMElement[] */
    private array $aIBSCBS = [];
    /** @var DOMElement[] */
    private array $aPIS = [];
    /** @var DOMElement[] */
    private array $aCOFINS = [];
    /** @var DOMElement[] */
    private array $aRetTrib = [];
    /** @var DOMElement[] */
    private array $aTFS = [];
    /** @var DOMElement[] */
    private array $aTFU = [];
    /** @var DOMElement[] */
    private array $aGProcRef = [];
    /** @var string[] */
    private array $aInfAdProd = [];

    private ?DOMElement $total = null;
    private ?DOMElement $vRetTribTot = null;
    private ?DOMElement $IBSCBSTot = null;

    private ?DOMElement $gFat = null;
    private ?DOMElement $gPIX = null;
    private ?DOMElement $enderCorrespFat = null;

    private ?DOMElement $gAgencia = null;
    /** @var DOMElement[] */
    private array $aGHistCons = [];
    /** @var array<int, DOMElement[]> */
    private array $aGCons = [];

    private ?DOMElement $gQualiAgua = null;
    /** @var DOMElement[] */
    private array $aGAnalise = [];

    /** @var DOMElement[] */
    private array $aAutXML = [];

    private ?DOMElement $infAdic = null;
    private ?DOMElement $infPAA = null;
    private ?DOMElement $gRespTec = null;
    private ?DOMElement $infNFAgSupl = null;

    private string $xml = '';

    public function __construct(string $version = '1.00')
    {
        $this->version = $version;
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = false;
        $this->dom->preserveWhiteSpace = false;
        $this->NFAg = $this->dom->createElement('NFAg');
        $this->NFAg->setAttribute('xmlns', self::NS);
    }

    /* ===================== infNFAg ===================== */

    public function taginfNFAg(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['Id']);
        $id = (string) $std->Id;
        // Compatibilidade: aceita "NFAg" minúsculo e converte para "NFAG" (padrão XSD).
        if (str_starts_with($id, 'NFAg')) {
            $id = 'NFAG' . substr($id, 4);
        }
        if (! str_starts_with($id, 'NFAG')) {
            throw new InvalidArgumentException('infNFAg/Id deve iniciar com "NFAG".');
        }
        if (strlen($id) !== 48) {
            $this->errors[] = "infNFAg/Id deve ter 48 caracteres (NFAG + 44 chars), recebido " . strlen($id);
        }
        if (! preg_match('/^NFAG[0-9]{6}[A-Z0-9]{12}[0-9]{26}$/', $id)) {
            $this->errors[] = "infNFAg/Id não casa com o padrão XSD NFAG[0-9]{6}[A-Z0-9]{12}[0-9]{26}.";
        }
        $this->chNFAg = substr($id, 4);
        $this->infNFAg = $this->dom->createElement('infNFAg');
        $this->infNFAg->setAttribute('Id', $id);
        $this->infNFAg->setAttribute('versao', $this->version);
        return $this->infNFAg;
    }

    /* ===================== ide ===================== */

    public function tagide(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'cUF','tpAmb','mod','serie','nNF','cNF','cDV','dhEmi','tpEmis',
            'nSiteAutoriz','cMunFG','finNFAg','tpFat','verProc','dhCont','xJust'
        ]);
        $ide = $this->dom->createElement('ide');
        $this->addChild($ide, 'cUF', $std->cUF, true, 'cUF');
        $this->addChild($ide, 'tpAmb', $std->tpAmb, true, 'tpAmb');
        $this->addChild($ide, 'mod', $std->mod ?? '75', true, 'mod');
        $this->addChild($ide, 'serie', $std->serie, true, 'serie');
        $this->addChild($ide, 'nNF', $std->nNF, true, 'nNF');
        $this->addChild($ide, 'cNF', str_pad((string) $std->cNF, 7, '0', STR_PAD_LEFT), true, 'cNF');
        $this->addChild($ide, 'cDV', $std->cDV, true, 'cDV');
        $this->addChild($ide, 'dhEmi', $std->dhEmi, true, 'dhEmi');
        $this->addChild($ide, 'tpEmis', $std->tpEmis, true, 'tpEmis');
        $this->addChild($ide, 'nSiteAutoriz', $std->nSiteAutoriz, true, 'nSiteAutoriz');
        $this->addChild($ide, 'cMunFG', $std->cMunFG, true, 'cMunFG');
        $this->addChild($ide, 'finNFAg', $std->finNFAg, true, 'finNFAg');
        $this->addChild($ide, 'tpFat', $std->tpFat, true, 'tpFat');
        $this->addChild($ide, 'verProc', $std->verProc, true, 'verProc');
        $this->addChild($ide, 'dhCont', $std->dhCont, false, 'dhCont');
        $this->addChild($ide, 'xJust', $std->xJust, false, 'xJust');
        $this->ide = $ide;
        return $ide;
    }

    public function taggCompraGov(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['tpEnteGov','pRedutor','tpOperGov']);
        $g = $this->dom->createElement('gCompraGov');
        $this->addChild($g, 'tpEnteGov', $std->tpEnteGov, true, 'tpEnteGov');
        $this->addChild($g, 'pRedutor', $std->pRedutor, true, 'pRedutor');
        $this->addChild($g, 'tpOperGov', $std->tpOperGov, true, 'tpOperGov');
        $this->gCompraGov = $g;
        return $g;
    }

    /* ===================== emit ===================== */

    public function tagemit(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['CNPJ','IE','xNome','xFant']);
        $emit = $this->dom->createElement('emit');
        $this->addChild($emit, 'CNPJ', $std->CNPJ, true, 'emit/CNPJ');
        $this->addChild($emit, 'IE', $std->IE, false, 'emit/IE');
        $this->addChild($emit, 'xNome', $std->xNome, true, 'emit/xNome');
        $this->addChild($emit, 'xFant', $std->xFant, false, 'emit/xFant');
        $this->emit = $emit;
        return $emit;
    }

    public function tagenderEmit(stdClass $std): DOMElement
    {
        $end = $this->buildEndereco('enderEmit', $std);
        $this->enderEmit = $end;
        return $end;
    }

    /* ===================== dest ===================== */

    public function tagdest(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'xNome','CNPJ','CPF','idOutros','IE','IM','cNIS','NB','xNomeAdicional'
        ]);
        $dest = $this->dom->createElement('dest');
        $this->addChild($dest, 'xNome', $std->xNome, true, 'dest/xNome');
        // choice: CNPJ | CPF | idOutros
        if ($std->CNPJ !== null) {
            $this->addChild($dest, 'CNPJ', $std->CNPJ, true, 'dest/CNPJ');
        } elseif ($std->CPF !== null) {
            $this->addChild($dest, 'CPF', $std->CPF, true, 'dest/CPF');
        } elseif ($std->idOutros !== null) {
            $this->addChild($dest, 'idOutros', $std->idOutros, true, 'dest/idOutros');
        }
        $this->addChild($dest, 'IE', $std->IE, false, 'dest/IE');
        $this->addChild($dest, 'IM', $std->IM, false, 'dest/IM');
        if ($std->cNIS !== null) {
            $this->addChild($dest, 'cNIS', $std->cNIS, false, 'dest/cNIS');
        } elseif ($std->NB !== null) {
            $this->addChild($dest, 'NB', $std->NB, false, 'dest/NB');
        }
        $this->addChild($dest, 'xNomeAdicional', $std->xNomeAdicional, false, 'dest/xNomeAdicional');
        $this->dest = $dest;
        return $dest;
    }

    public function tagenderDest(stdClass $std): DOMElement
    {
        $end = $this->buildEndereco('enderDest', $std);
        $this->enderDest = $end;
        return $end;
    }

    /* ===================== ligacao ===================== */

    public function tagligacao(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'idLigacao','idCodCliente','tpLigacao','latGPS','longGPS','codRoteiroLeitura'
        ]);
        $g = $this->dom->createElement('ligacao');
        $this->addChild($g, 'idLigacao', $std->idLigacao, true, 'ligacao/idLigacao');
        $this->addChild($g, 'idCodCliente', $std->idCodCliente, false, 'ligacao/idCodCliente');
        $this->addChild($g, 'tpLigacao', $std->tpLigacao, true, 'ligacao/tpLigacao');
        $this->addChild($g, 'latGPS', $std->latGPS, true, 'ligacao/latGPS');
        $this->addChild($g, 'longGPS', $std->longGPS, true, 'ligacao/longGPS');
        $this->addChild($g, 'codRoteiroLeitura', $std->codRoteiroLeitura, false, 'ligacao/codRoteiroLeitura');
        $this->ligacao = $g;
        return $g;
    }

    /* ===================== gSub ===================== */

    public function taggSub(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['chNFAg','motSub']);
        $g = $this->dom->createElement('gSub');
        $this->addChild($g, 'chNFAg', $std->chNFAg, true, 'gSub/chNFAg');
        $this->addChild($g, 'motSub', $std->motSub, true, 'gSub/motSub');
        $this->gSub = $g;
        return $g;
    }

    /* ===================== gMed (0..99) ===================== */

    public function taggMed(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['nMed','idMedidor','dMedAnt','dMedAtu']);
        $g = $this->dom->createElement('gMed');
        $g->setAttribute('nMed', str_pad((string) $std->nMed, 2, '0', STR_PAD_LEFT));
        $this->addChild($g, 'idMedidor', $std->idMedidor, true, 'gMed/idMedidor');
        $this->addChild($g, 'dMedAnt', $std->dMedAnt, true, 'gMed/dMedAnt');
        $this->addChild($g, 'dMedAtu', $std->dMedAtu, true, 'gMed/dMedAtu');
        $this->aGMed[] = $g;
        return $g;
    }

    /* ===================== gFatConjunto ===================== */

    public function taggFatConjunto(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['chNFAgFat']);
        $g = $this->dom->createElement('gFatConjunto');
        $this->addChild($g, 'chNFAgFat', $std->chNFAgFat, true, 'gFatConjunto/chNFAgFat');
        $this->gFatConjunto = $g;
        return $g;
    }

    /* ===================== det (1..990) ===================== */

    public function tagdet(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['nItem','chNFAgAnt','nItemAnt']);
        $nItem = (int) $std->nItem;
        if ($nItem < 1 || $nItem > 990) {
            throw new InvalidArgumentException('det/nItem deve estar entre 1 e 990.');
        }
        $det = $this->dom->createElement('det');
        $det->setAttribute('nItem', (string) $nItem);
        if ($std->chNFAgAnt !== null) {
            $det->setAttribute('chNFAgAnt', (string) $std->chNFAgAnt);
        }
        if ($std->nItemAnt !== null) {
            $det->setAttribute('nItemAnt', (string) $std->nItemAnt);
        }
        $this->aDet[$nItem] = $det;
        return $det;
    }

    public function taggTarif(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'item','dIniTarif','dFimTarif','nAto','anoAto','tpFaixaCons'
        ]);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('gTarif');
        $this->addChild($g, 'dIniTarif', $std->dIniTarif, true, 'gTarif/dIniTarif');
        $this->addChild($g, 'dFimTarif', $std->dFimTarif, false, 'gTarif/dFimTarif');
        $this->addChild($g, 'nAto', $std->nAto, true, 'gTarif/nAto');
        $this->addChild($g, 'anoAto', $std->anoAto, true, 'gTarif/anoAto');
        $this->addChild($g, 'tpFaixaCons', $std->tpFaixaCons, false, 'gTarif/tpFaixaCons');
        $this->aGTarif[$nItem][] = $g;
        return $g;
    }

    public function tagprod(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'item','indOrigemQtd','cProd','xProd','cClass','tpCategoria','xCategoria',
            'qEconomias','uMed','qFaturada','vItem','fatorPoluicao','vProd','indDevolucao'
        ]);
        $nItem = (int) $std->item;
        $prod = $this->dom->createElement('prod');
        $this->addChild($prod, 'indOrigemQtd', $std->indOrigemQtd, true, 'prod/indOrigemQtd');
        // gMedicao é adicionado depois por taggMedicao
        $this->addChild($prod, 'cProd', $std->cProd, true, 'prod/cProd');
        $this->addChild($prod, 'xProd', $std->xProd, true, 'prod/xProd');
        $this->addChild($prod, 'cClass', $std->cClass, true, 'prod/cClass');
        if ($std->tpCategoria !== null) {
            $this->addChild($prod, 'tpCategoria', $std->tpCategoria, true, 'prod/tpCategoria');
            $this->addChild($prod, 'xCategoria', $std->xCategoria, false, 'prod/xCategoria');
            $this->addChild($prod, 'qEconomias', $std->qEconomias, false, 'prod/qEconomias');
        }
        $this->addChild($prod, 'uMed', $std->uMed, true, 'prod/uMed');
        $this->addChild($prod, 'qFaturada', $std->qFaturada, true, 'prod/qFaturada');
        $this->addChild($prod, 'vItem', $std->vItem, true, 'prod/vItem');
        $this->addChild($prod, 'fatorPoluicao', $std->fatorPoluicao, false, 'prod/fatorPoluicao');
        $this->addChild($prod, 'vProd', $std->vProd, true, 'prod/vProd');
        $this->addChild($prod, 'indDevolucao', $std->indDevolucao, false, 'prod/indDevolucao');
        $this->aProd[$nItem] = $prod;
        return $prod;
    }

    public function taggMedicao(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'item','nMed','gMedida','tpMotNaoLeitura'
        ]);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('gMedicao');
        $this->addChild($g, 'nMed', str_pad((string) $std->nMed, 2, '0', STR_PAD_LEFT), true, 'gMedicao/nMed');
        // choice: gMedida (com submedidas) OU tpMotNaoLeitura
        if (! empty($std->gMedida)) {
            $gMedida = $this->dom->createElement('gMedida');
            foreach ((array) $std->gMedida as $k => $v) {
                $this->addChild($gMedida, $k, $v, false, "gMedida/{$k}");
            }
            $g->appendChild($gMedida);
        } elseif ($std->tpMotNaoLeitura !== null) {
            $this->addChild($g, 'tpMotNaoLeitura', $std->tpMotNaoLeitura, true, 'gMedicao/tpMotNaoLeitura');
        }
        $this->aGMedicao[$nItem] = $g;
        return $g;
    }

    public function tagimposto(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['item']);
        $nItem = (int) $std->item;
        $imposto = $this->dom->createElement('imposto');
        $this->aImposto[$nItem] = $imposto;
        return $imposto;
    }

    public function tagIBSCBS(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'item','CST','cClassTrib','indDoacao','gIBSCBS','gEstornoCred'
        ]);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('IBSCBS');
        $this->addChild($g, 'CST', $std->CST, true, 'IBSCBS/CST');
        $this->addChild($g, 'cClassTrib', $std->cClassTrib, true, 'IBSCBS/cClassTrib');
        $this->addChild($g, 'indDoacao', $std->indDoacao, false, 'IBSCBS/indDoacao');
        if (! empty($std->gIBSCBS) && is_array($std->gIBSCBS)) {
            $g->appendChild($this->buildSubtree('gIBSCBS', $std->gIBSCBS));
        }
        if (! empty($std->gEstornoCred) && is_array($std->gEstornoCred)) {
            $g->appendChild($this->buildSubtree('gEstornoCred', $std->gEstornoCred));
        }
        $this->aIBSCBS[$nItem] = $g;
        return $g;
    }

    public function tagPIS(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['item','CST','vBC','pPIS','vPIS']);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('PIS');
        $this->addChild($g, 'CST', $std->CST, true, 'PIS/CST');
        $this->addChild($g, 'vBC', $std->vBC, true, 'PIS/vBC');
        $this->addChild($g, 'pPIS', $std->pPIS, true, 'PIS/pPIS');
        $this->addChild($g, 'vPIS', $std->vPIS, true, 'PIS/vPIS');
        $this->aPIS[$nItem] = $g;
        return $g;
    }

    public function tagCOFINS(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['item','CST','vBC','pCOFINS','vCOFINS']);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('COFINS');
        $this->addChild($g, 'CST', $std->CST, true, 'COFINS/CST');
        $this->addChild($g, 'vBC', $std->vBC, true, 'COFINS/vBC');
        $this->addChild($g, 'pCOFINS', $std->pCOFINS, true, 'COFINS/pCOFINS');
        $this->addChild($g, 'vCOFINS', $std->vCOFINS, true, 'COFINS/vCOFINS');
        $this->aCOFINS[$nItem] = $g;
        return $g;
    }

    public function tagretTrib(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['item','vRetPIS','vRetCofins','vRetCSLL','vIRRF']);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('retTrib');
        $this->addChild($g, 'vRetPIS', $std->vRetPIS, true, 'retTrib/vRetPIS');
        $this->addChild($g, 'vRetCofins', $std->vRetCofins, true, 'retTrib/vRetCofins');
        $this->addChild($g, 'vRetCSLL', $std->vRetCSLL, true, 'retTrib/vRetCSLL');
        $this->addChild($g, 'vIRRF', $std->vIRRF, true, 'retTrib/vIRRF');
        $this->aRetTrib[$nItem] = $g;
        return $g;
    }

    public function tagTFS(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['item','vBCTFS','pTFS','vTFS']);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('TFS');
        $this->addChild($g, 'vBCTFS', $std->vBCTFS, true, 'TFS/vBCTFS');
        $this->addChild($g, 'pTFS', $std->pTFS, true, 'TFS/pTFS');
        $this->addChild($g, 'vTFS', $std->vTFS, true, 'TFS/vTFS');
        $this->aTFS[$nItem] = $g;
        return $g;
    }

    public function tagTFU(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['item','vBCTFU','pTFU','vTFU']);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('TFU');
        $this->addChild($g, 'vBCTFU', $std->vBCTFU, true, 'TFU/vBCTFU');
        $this->addChild($g, 'pTFU', $std->pTFU, true, 'TFU/pTFU');
        $this->addChild($g, 'vTFU', $std->vTFU, true, 'TFU/vTFU');
        $this->aTFU[$nItem] = $g;
        return $g;
    }

    public function taggProcRef(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'item','vItem','qFaturada','vProd','indDevolucao','gProc'
        ]);
        $nItem = (int) $std->item;
        $g = $this->dom->createElement('gProcRef');
        $this->addChild($g, 'vItem', $std->vItem, true, 'gProcRef/vItem');
        $this->addChild($g, 'qFaturada', $std->qFaturada, true, 'gProcRef/qFaturada');
        $this->addChild($g, 'vProd', $std->vProd, true, 'gProcRef/vProd');
        $this->addChild($g, 'indDevolucao', $std->indDevolucao, false, 'gProcRef/indDevolucao');
        if (! empty($std->gProc) && is_array($std->gProc)) {
            foreach ($std->gProc as $proc) {
                $procArr = (array) $proc;
                $gProc = $this->dom->createElement('gProc');
                $this->addChild($gProc, 'tpProc', $procArr['tpProc'] ?? null, true, 'gProc/tpProc');
                $this->addChild($gProc, 'nProcesso', $procArr['nProcesso'] ?? null, true, 'gProc/nProcesso');
                $g->appendChild($gProc);
            }
        }
        $this->aGProcRef[$nItem] = $g;
        return $g;
    }

    public function taginfAdProd(stdClass $std): void
    {
        $std = $this->equilizeParameters($std, ['item','infAdProd']);
        $this->aInfAdProd[(int) $std->item] = (string) $std->infAdProd;
    }

    /* ===================== total ===================== */

    public function tagtotal(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'vProd','vCOFINS','vPIS','vTFS','vTFU','vNF','vTotDFe'
        ]);
        $total = $this->dom->createElement('total');
        $this->addChild($total, 'vProd', $std->vProd, true, 'total/vProd');
        // vRetTribTot e IBSCBSTot são adicionados depois via tag específicas
        $this->total = $total;
        // Armazena os campos restantes em propriedades temporárias via attribute
        $total->setAttribute('_vCOFINS', (string) ($std->vCOFINS ?? ''));
        $total->setAttribute('_vPIS', (string) ($std->vPIS ?? ''));
        $total->setAttribute('_vTFS', (string) ($std->vTFS ?? ''));
        $total->setAttribute('_vTFU', (string) ($std->vTFU ?? ''));
        $total->setAttribute('_vNF', (string) ($std->vNF ?? ''));
        $total->setAttribute('_vTotDFe', (string) ($std->vTotDFe ?? ''));
        return $total;
    }

    public function tagvRetTribTot(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['vRetPIS','vRetCofins','vRetCSLL','vIRRF']);
        $g = $this->dom->createElement('vRetTribTot');
        $this->addChild($g, 'vRetPIS', $std->vRetPIS, true, 'vRetTribTot/vRetPIS');
        $this->addChild($g, 'vRetCofins', $std->vRetCofins, true, 'vRetTribTot/vRetCofins');
        $this->addChild($g, 'vRetCSLL', $std->vRetCSLL, true, 'vRetTribTot/vRetCSLL');
        $this->addChild($g, 'vIRRF', $std->vIRRF, true, 'vRetTribTot/vIRRF');
        $this->vRetTribTot = $g;
        return $g;
    }

    public function tagIBSCBSTot(array $data): DOMElement
    {
        $g = $this->buildSubtree('IBSCBSTot', $data);
        $this->IBSCBSTot = $g;
        return $g;
    }

    /* ===================== gFat ===================== */

    public function taggFat(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'CompetFat','dVencFat','dApresFat','dProxLeitura','nFat',
            'codBarras','codDebAuto','codBanco','codAgencia'
        ]);
        $g = $this->dom->createElement('gFat');
        $this->addChild($g, 'CompetFat', $std->CompetFat, true, 'gFat/CompetFat');
        $this->addChild($g, 'dVencFat', $std->dVencFat, true, 'gFat/dVencFat');
        $this->addChild($g, 'dApresFat', $std->dApresFat, false, 'gFat/dApresFat');
        $this->addChild($g, 'dProxLeitura', $std->dProxLeitura, true, 'gFat/dProxLeitura');
        $this->addChild($g, 'nFat', $std->nFat, false, 'gFat/nFat');
        $this->addChild($g, 'codBarras', $std->codBarras, true, 'gFat/codBarras');
        if ($std->codDebAuto !== null) {
            $this->addChild($g, 'codDebAuto', $std->codDebAuto, false, 'gFat/codDebAuto');
        } elseif ($std->codBanco !== null) {
            $this->addChild($g, 'codBanco', $std->codBanco, true, 'gFat/codBanco');
            $this->addChild($g, 'codAgencia', $std->codAgencia, true, 'gFat/codAgencia');
        }
        $this->gFat = $g;
        return $g;
    }

    public function tagenderCorrespFat(stdClass $std): DOMElement
    {
        $end = $this->buildEndereco('enderCorresp', $std);
        $this->enderCorrespFat = $end;
        return $end;
    }

    public function taggPIX(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['urlQRCodePIX']);
        $g = $this->dom->createElement('gPIX');
        $this->addChild($g, 'urlQRCodePIX', $std->urlQRCodePIX, true, 'gPIX/urlQRCodePIX');
        $this->gPIX = $g;
        return $g;
    }

    /* ===================== gAgencia ===================== */

    public function taggAgencia(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'econ','econAcumulada','sPrestador','dEmissSelo','sRegulador',
            'nAgenciaAtend','enderAgenciaAtend'
        ]);
        $g = $this->dom->createElement('gAgencia');
        $this->addChild($g, 'econ', $std->econ, false, 'gAgencia/econ');
        $this->addChild($g, 'econAcumulada', $std->econAcumulada, false, 'gAgencia/econAcumulada');
        $this->addChild($g, 'sPrestador', $std->sPrestador, false, 'gAgencia/sPrestador');
        $this->addChild($g, 'dEmissSelo', $std->dEmissSelo, false, 'gAgencia/dEmissSelo');
        $this->addChild($g, 'sRegulador', $std->sRegulador, false, 'gAgencia/sRegulador');
        $this->addChild($g, 'nAgenciaAtend', $std->nAgenciaAtend, true, 'gAgencia/nAgenciaAtend');
        $this->addChild($g, 'enderAgenciaAtend', $std->enderAgenciaAtend, true, 'gAgencia/enderAgenciaAtend');
        $this->gAgencia = $g;
        return $g;
    }

    public function taggHistCons(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['xHistorico','medMensal']);
        $g = $this->dom->createElement('gHistCons');
        $this->addChild($g, 'xHistorico', $std->xHistorico, true, 'gHistCons/xHistorico');
        // gCons são adicionados depois
        $idx = count($this->aGHistCons);
        $this->aGHistCons[$idx] = $g;
        // medMensal será inserido no monta() depois dos gCons
        $g->setAttribute('_medMensal', (string) $std->medMensal);
        return $g;
    }

    public function taggCons(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'idxHist','CompetFat','uMed','qtdDias','medDiaria','consumo','volFat'
        ]);
        $idx = (int) $std->idxHist;
        $g = $this->dom->createElement('gCons');
        $this->addChild($g, 'CompetFat', $std->CompetFat, true, 'gCons/CompetFat');
        $this->addChild($g, 'uMed', $std->uMed, true, 'gCons/uMed');
        $this->addChild($g, 'qtdDias', $std->qtdDias, true, 'gCons/qtdDias');
        $this->addChild($g, 'medDiaria', $std->medDiaria, false, 'gCons/medDiaria');
        $this->addChild($g, 'consumo', $std->consumo, false, 'gCons/consumo');
        $this->addChild($g, 'volFat', $std->volFat, true, 'gCons/volFat');
        $this->aGCons[$idx][] = $g;
        return $g;
    }

    /* ===================== gQualiAgua ===================== */

    public function taggQualiAgua(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['CompetAnalise']);
        $g = $this->dom->createElement('gQualiAgua');
        $this->addChild($g, 'CompetAnalise', $std->CompetAnalise, true, 'gQualiAgua/CompetAnalise');
        $this->gQualiAgua = $g;
        return $g;
    }

    public function taggAnalise(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'xItemAnalisado','nAmostraMinima','nAmostraAnalisada',
            'nAmostraFPadrao','nAmostraDPadrao','nMediaMensal','xValorReferencia'
        ]);
        $g = $this->dom->createElement('gAnalise');
        $this->addChild($g, 'xItemAnalisado', $std->xItemAnalisado, true, 'gAnalise/xItemAnalisado');
        $this->addChild($g, 'nAmostraMinima', $std->nAmostraMinima, false, 'gAnalise/nAmostraMinima');
        $this->addChild($g, 'nAmostraAnalisada', $std->nAmostraAnalisada, false, 'gAnalise/nAmostraAnalisada');
        $this->addChild($g, 'nAmostraFPadrao', $std->nAmostraFPadrao, false, 'gAnalise/nAmostraFPadrao');
        $this->addChild($g, 'nAmostraDPadrao', $std->nAmostraDPadrao, false, 'gAnalise/nAmostraDPadrao');
        $this->addChild($g, 'nMediaMensal', $std->nMediaMensal, false, 'gAnalise/nMediaMensal');
        $this->addChild($g, 'xValorReferencia', $std->xValorReferencia, false, 'gAnalise/xValorReferencia');
        $this->aGAnalise[] = $g;
        return $g;
    }

    /* ===================== autXML ===================== */

    public function tagautXML(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['CNPJ','CPF']);
        $g = $this->dom->createElement('autXML');
        if ($std->CNPJ !== null) {
            $this->addChild($g, 'CNPJ', $std->CNPJ, true, 'autXML/CNPJ');
        } elseif ($std->CPF !== null) {
            $this->addChild($g, 'CPF', $std->CPF, true, 'autXML/CPF');
        }
        $this->aAutXML[] = $g;
        return $g;
    }

    /* ===================== infAdic ===================== */

    public function taginfAdic(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['infAdFisco','infCpl']);
        $g = $this->dom->createElement('infAdic');
        $this->addChild($g, 'infAdFisco', $std->infAdFisco, false, 'infAdic/infAdFisco');
        if (! empty($std->infCpl)) {
            $list = is_array($std->infCpl) ? $std->infCpl : [$std->infCpl];
            foreach ($list as $cpl) {
                $this->addChild($g, 'infCpl', $cpl, false, 'infAdic/infCpl');
            }
        }
        $this->infAdic = $g;
        return $g;
    }

    /* ===================== infPAA ===================== */

    public function taginfPAA(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['CNPJPAA']);
        $g = $this->dom->createElement('infPAA');
        $this->addChild($g, 'CNPJPAA', $std->CNPJPAA, true, 'infPAA/CNPJPAA');
        $this->infPAA = $g;
        return $g;
    }

    /* ===================== gRespTec ===================== */

    public function taggRespTec(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'CNPJ','xContato','email','fone','idCSRT','hashCSRT'
        ]);
        $g = $this->dom->createElement('gRespTec');
        $this->addChild($g, 'CNPJ', $std->CNPJ, true, 'gRespTec/CNPJ');
        $this->addChild($g, 'xContato', $std->xContato, true, 'gRespTec/xContato');
        $this->addChild($g, 'email', $std->email, true, 'gRespTec/email');
        $this->addChild($g, 'fone', $std->fone, true, 'gRespTec/fone');
        $this->addChild($g, 'idCSRT', $std->idCSRT, false, 'gRespTec/idCSRT');
        $this->addChild($g, 'hashCSRT', $std->hashCSRT, false, 'gRespTec/hashCSRT');
        $this->gRespTec = $g;
        return $g;
    }

    /* ===================== infNFAgSupl ===================== */

    public function taginfNFAgSupl(stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, ['qrCodNFAg']);
        $g = $this->dom->createElement('infNFAgSupl');
        $this->addChild($g, 'qrCodNFAg', $std->qrCodNFAg, true, 'infNFAgSupl/qrCodNFAg');
        $this->infNFAgSupl = $g;
        return $g;
    }

    /* ===================== Build / monta ===================== */

    /**
     * Monta o XML final, respeitando a ordem do <xs:sequence/> de cada complexType.
     */
    public function monta(): string
    {
        $this->errors = $this->validateRequiredBlocks($this->errors);
        if ($this->errors !== []) {
            throw new RuntimeException('Erros ao montar NFAg: ' . implode(' | ', $this->errors));
        }

        // ide: append gCompraGov se houver
        if ($this->gCompraGov !== null && $this->ide !== null) {
            $this->ide->appendChild($this->gCompraGov);
        }

        // emit
        if ($this->emit !== null && $this->enderEmit !== null) {
            $this->emit->appendChild($this->enderEmit);
        }

        // dest
        if ($this->dest !== null && $this->enderDest !== null) {
            $this->dest->appendChild($this->enderDest);
        }

        // det: prod (com gMedicao no lugar correto), imposto (IBSCBS, PIS, COFINS, retTrib, TFS, TFU), gProcRef, infAdProd
        ksort($this->aDet);
        foreach ($this->aDet as $nItem => $det) {
            // gTarif (antes do prod)
            if (! empty($this->aGTarif[$nItem])) {
                foreach ($this->aGTarif[$nItem] as $gT) {
                    $det->appendChild($gT);
                }
            }
            $prod = $this->aProd[$nItem] ?? null;
            if ($prod !== null) {
                // Inserir gMedicao logo após indOrigemQtd (1º elemento)
                if (! empty($this->aGMedicao[$nItem])) {
                    $first = $prod->firstChild; // indOrigemQtd
                    if ($first && $first->nextSibling) {
                        $prod->insertBefore($this->aGMedicao[$nItem], $first->nextSibling);
                    } else {
                        $prod->appendChild($this->aGMedicao[$nItem]);
                    }
                }
                $det->appendChild($prod);
            }
            $imposto = $this->aImposto[$nItem] ?? null;
            if ($imposto !== null) {
                if (! empty($this->aIBSCBS[$nItem])) {
                    $imposto->appendChild($this->aIBSCBS[$nItem]);
                }
                if (! empty($this->aPIS[$nItem])) {
                    $imposto->appendChild($this->aPIS[$nItem]);
                }
                if (! empty($this->aCOFINS[$nItem])) {
                    $imposto->appendChild($this->aCOFINS[$nItem]);
                }
                if (! empty($this->aRetTrib[$nItem])) {
                    $imposto->appendChild($this->aRetTrib[$nItem]);
                }
                if (! empty($this->aTFS[$nItem])) {
                    $imposto->appendChild($this->aTFS[$nItem]);
                }
                if (! empty($this->aTFU[$nItem])) {
                    $imposto->appendChild($this->aTFU[$nItem]);
                }
                $det->appendChild($imposto);
            }
            if (! empty($this->aGProcRef[$nItem])) {
                $det->appendChild($this->aGProcRef[$nItem]);
            }
            if (! empty($this->aInfAdProd[$nItem])) {
                $this->addChild($det, 'infAdProd', $this->aInfAdProd[$nItem], false, 'det/infAdProd');
            }
        }

        // total: vProd já está, agora vRetTribTot, vCOFINS, vPIS, vTFS, vTFU, vNF, IBSCBSTot, vTotDFe
        if ($this->total !== null) {
            $stash = [
                'vCOFINS'  => $this->total->getAttribute('_vCOFINS'),
                'vPIS'     => $this->total->getAttribute('_vPIS'),
                'vTFS'     => $this->total->getAttribute('_vTFS'),
                'vTFU'     => $this->total->getAttribute('_vTFU'),
                'vNF'      => $this->total->getAttribute('_vNF'),
                'vTotDFe'  => $this->total->getAttribute('_vTotDFe'),
            ];
            foreach (array_keys($stash) as $k) {
                $this->total->removeAttribute("_{$k}");
            }
            if ($this->vRetTribTot !== null) {
                $this->total->appendChild($this->vRetTribTot);
            }
            if ($stash['vCOFINS'] !== '') $this->addChild($this->total, 'vCOFINS', $stash['vCOFINS'], true, 'total/vCOFINS');
            if ($stash['vPIS'] !== '')    $this->addChild($this->total, 'vPIS', $stash['vPIS'], true, 'total/vPIS');
            if ($stash['vTFS'] !== '')    $this->addChild($this->total, 'vTFS', $stash['vTFS'], true, 'total/vTFS');
            if ($stash['vTFU'] !== '')    $this->addChild($this->total, 'vTFU', $stash['vTFU'], true, 'total/vTFU');
            if ($stash['vNF'] !== '')     $this->addChild($this->total, 'vNF', $stash['vNF'], true, 'total/vNF');
            if ($this->IBSCBSTot !== null) {
                $this->total->appendChild($this->IBSCBSTot);
            }
            if ($stash['vTotDFe'] !== '') $this->addChild($this->total, 'vTotDFe', $stash['vTotDFe'], true, 'total/vTotDFe');
        }

        // gFat: enderCorresp e gPIX no fim
        if ($this->gFat !== null) {
            if ($this->enderCorrespFat !== null) {
                $this->gFat->appendChild($this->enderCorrespFat);
            }
            if ($this->gPIX !== null) {
                $this->gFat->appendChild($this->gPIX);
            }
        }

        // gAgencia: gHistCons (com gCons + medMensal)
        if ($this->gAgencia !== null) {
            foreach ($this->aGHistCons as $idx => $gHist) {
                $medMensal = $gHist->getAttribute('_medMensal');
                $gHist->removeAttribute('_medMensal');
                if (! empty($this->aGCons[$idx])) {
                    foreach ($this->aGCons[$idx] as $gC) {
                        $gHist->appendChild($gC);
                    }
                }
                if ($medMensal !== '') {
                    $this->addChild($gHist, 'medMensal', $medMensal, true, 'gHistCons/medMensal');
                }
                $this->gAgencia->appendChild($gHist);
            }
        }

        // gQualiAgua: gAnalise
        if ($this->gQualiAgua !== null) {
            foreach ($this->aGAnalise as $gA) {
                $this->gQualiAgua->appendChild($gA);
            }
        }

        // infNFAg: ordem (ide, emit, dest, ligacao, gSub?, gMed*, gFatConjunto?, det+, total, gFat?, gAgencia, gQualiAgua?, autXML*, infAdic?, infPAA?, gRespTec?)
        if ($this->infNFAg === null) {
            throw new RuntimeException('infNFAg() não foi chamado (call taginfNFAg primeiro).');
        }
        if ($this->ide !== null) $this->infNFAg->appendChild($this->ide);
        if ($this->emit !== null) $this->infNFAg->appendChild($this->emit);
        if ($this->dest !== null) $this->infNFAg->appendChild($this->dest);
        if ($this->ligacao !== null) $this->infNFAg->appendChild($this->ligacao);
        if ($this->gSub !== null) $this->infNFAg->appendChild($this->gSub);
        foreach ($this->aGMed as $gM) $this->infNFAg->appendChild($gM);
        if ($this->gFatConjunto !== null) $this->infNFAg->appendChild($this->gFatConjunto);
        foreach ($this->aDet as $det) $this->infNFAg->appendChild($det);
        if ($this->total !== null) $this->infNFAg->appendChild($this->total);
        if ($this->gFat !== null) $this->infNFAg->appendChild($this->gFat);
        if ($this->gAgencia !== null) $this->infNFAg->appendChild($this->gAgencia);
        if ($this->gQualiAgua !== null) $this->infNFAg->appendChild($this->gQualiAgua);
        foreach ($this->aAutXML as $a) $this->infNFAg->appendChild($a);
        if ($this->infAdic !== null) $this->infNFAg->appendChild($this->infAdic);
        if ($this->infPAA !== null) $this->infNFAg->appendChild($this->infPAA);
        if ($this->gRespTec !== null) $this->infNFAg->appendChild($this->gRespTec);

        // NFAg: infNFAg, infNFAgSupl
        $this->NFAg->appendChild($this->infNFAg);
        if ($this->infNFAgSupl !== null) {
            $this->NFAg->appendChild($this->infNFAgSupl);
        }
        $this->dom->appendChild($this->NFAg);
        $this->xml = (string) $this->dom->saveXML($this->dom->documentElement);
        return $this->xml;
    }

    public function getXML(): string
    {
        if ($this->xml === '') {
            $this->monta();
        }
        return $this->xml;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getChave(): string
    {
        return $this->chNFAg;
    }

    /* ===================== Helpers ===================== */

    /**
     * Garante que todas as propriedades existam (null caso ausentes).
     */
    private function equilizeParameters(stdClass $std, array $fields): stdClass
    {
        foreach ($fields as $f) {
            if (! property_exists($std, $f)) {
                $std->{$f} = null;
            }
        }
        return $std;
    }

    /**
     * Adiciona um filho de texto se o valor não for nulo/string vazia.
     * Se obrigatório e ausente, registra erro.
     */
    private function addChild(DOMElement $parent, string $name, mixed $value, bool $required, string $path): void
    {
        if ($value === null || $value === '') {
            if ($required) {
                $this->errors[] = "Campo obrigatório ausente: {$path}";
            }
            return;
        }
        $child = $this->dom->createElement($name, htmlspecialchars((string) $value, ENT_XML1));
        $parent->appendChild($child);
    }

    /**
     * Constrói uma subárvore genérica a partir de array associativo.
     * Útil para grupos IBS/CBS aninhados.
     */
    private function buildSubtree(string $name, array $data): DOMElement
    {
        $node = $this->dom->createElement($name);
        foreach ($data as $k => $v) {
            $k = (string) $k;
            if (is_array($v)) {
                if ($this->isList($v)) {
                    foreach ($v as $item) {
                        $node->appendChild($this->buildSubtree($k, (array) $item));
                    }
                } else {
                    $node->appendChild($this->buildSubtree($k, $v));
                }
            } elseif ($v !== null && $v !== '') {
                $c = $this->dom->createElement($k, htmlspecialchars((string) $v, ENT_XML1));
                $node->appendChild($c);
            }
        }
        return $node;
    }

    private function isList(array $arr): bool
    {
        if ($arr === []) return false;
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * Constrói endereço seguindo a ordem do XSD.
     * Para enderEmit/enderDest (TEndeEmi): xLgr,nro,xCpl?,xBairro,cMun,xMun,CEP,UF,fone?,email?
     * Para enderCorresp (TEndereco): inclui cPais? e xPais? (CEP é opcional, vem antes de UF também).
     */
    private function buildEndereco(string $tag, stdClass $std): DOMElement
    {
        $std = $this->equilizeParameters($std, [
            'xLgr','nro','xCpl','xBairro','cMun','xMun','CEP','UF','cPais','xPais','fone','email'
        ]);
        $end = $this->dom->createElement($tag);
        $isEnderCorresp = ($tag === 'enderCorresp');
        $this->addChild($end, 'xLgr', $std->xLgr, true, "{$tag}/xLgr");
        $this->addChild($end, 'nro', $std->nro, true, "{$tag}/nro");
        $this->addChild($end, 'xCpl', $std->xCpl, false, "{$tag}/xCpl");
        $this->addChild($end, 'xBairro', $std->xBairro, true, "{$tag}/xBairro");
        $this->addChild($end, 'cMun', $std->cMun, true, "{$tag}/cMun");
        $this->addChild($end, 'xMun', $std->xMun, true, "{$tag}/xMun");
        // CEP: obrigatório em TEndeEmi, opcional em TEndereco
        $this->addChild($end, 'CEP', $std->CEP, ! $isEnderCorresp, "{$tag}/CEP");
        $this->addChild($end, 'UF', $std->UF, true, "{$tag}/UF");
        if ($isEnderCorresp) {
            // cPais/xPais só existem em TEndereco
            $this->addChild($end, 'cPais', $std->cPais, false, "{$tag}/cPais");
            $this->addChild($end, 'xPais', $std->xPais, false, "{$tag}/xPais");
        }
        $this->addChild($end, 'fone', $std->fone, false, "{$tag}/fone");
        $this->addChild($end, 'email', $std->email, false, "{$tag}/email");
        return $end;
    }

    private function validateRequiredBlocks(array $errors): array
    {
        if ($this->ide === null)    $errors[] = 'Bloco ide() obrigatório.';
        if ($this->emit === null)   $errors[] = 'Bloco emit() obrigatório.';
        if ($this->enderEmit === null) $errors[] = 'Bloco enderEmit() obrigatório.';
        if ($this->dest === null)   $errors[] = 'Bloco dest() obrigatório.';
        if ($this->enderDest === null) $errors[] = 'Bloco enderDest() obrigatório.';
        if ($this->ligacao === null) $errors[] = 'Bloco ligacao() obrigatório.';
        if ($this->aDet === [])     $errors[] = 'Pelo menos um det() obrigatório.';
        if ($this->total === null)  $errors[] = 'Bloco total() obrigatório.';
        if ($this->gAgencia === null) $errors[] = 'Bloco gAgencia() obrigatório.';
        if ($this->infNFAgSupl === null) $errors[] = 'Bloco infNFAgSupl() obrigatório (com qrCodNFAg).';
        return $errors;
    }
}
