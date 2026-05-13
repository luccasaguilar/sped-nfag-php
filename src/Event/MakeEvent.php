<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Event;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use stdClass;

/**
 * Builder de eventos da NFAg conforme eventoNFAg_v1.00.xsd / evCancNFAg_v1.00.xsd.
 *
 * Estrutura (TEvento):
 *   eventoNFAg[versao]
 *     infEvento[Id]
 *       cOrgao, tpAmb, CNPJ, chNFAg, dhEvento, tpEvento, nSeqEvento
 *       detEvento[versaoEvento] (xs:any)
 *       infPAA? (CNPJPAA)
 *     ds:Signature  (gerado pelo Signer)
 */
final class MakeEvent
{
    public const NS = 'http://www.portalfiscal.inf.br/nfag';

    private string $version;

    public function __construct(string $version = '1.00')
    {
        $this->version = $version;
    }

    /**
     * Gera o evento de Cancelamento (tpEvento=110111).
     * O conteúdo é envelopado em <evCancNFAg> dentro de <detEvento>.
     *
     * $std deve conter:
     *   cOrgao, tpAmb, CNPJ, chNFAg, dhEvento, nSeqEvento, nProt, xJust
     *   (opcionais) tpEvento, CNPJPAA
     */
    public function tagCancelamento(stdClass $std): string
    {
        $tpEvento = (string) ($std->tpEvento ?? '110111');
        return $this->build(
            tpEvento: $tpEvento,
            std: $std,
            wrapper: 'evCancNFAg',
            detChildren: [
                'descEvento' => 'Cancelamento',
                'nProt'      => $std->nProt ?? null,
                'xJust'      => $std->xJust ?? null,
            ],
        );
    }

    /**
     * Constrói um evento genérico. $wrapper é o elemento que envelopa
     * os filhos dentro de <detEvento> (ex: "evCancNFAg").
     *
     * @param array<string,scalar|null> $detChildren  Elementos do wrapper
     */
    public function tagEventoGenerico(
        stdClass $std,
        string $tpEvento,
        string $wrapper,
        array $detChildren
    ): string {
        return $this->build($tpEvento, $std, $wrapper, $detChildren);
    }

    private function build(string $tpEvento, stdClass $std, string $wrapper, array $detChildren): string
    {
        foreach (['cOrgao','tpAmb','CNPJ','chNFAg','dhEvento','nSeqEvento'] as $f) {
            if (! isset($std->{$f}) || $std->{$f} === '') {
                throw new InvalidArgumentException("Campo obrigatório ausente no evento: {$f}");
            }
        }
        if (strlen((string) $std->chNFAg) !== 44) {
            throw new InvalidArgumentException('chNFAg deve ter 44 dígitos.');
        }
        if (! preg_match('/^[0-9]{6}$/', $tpEvento)) {
            throw new InvalidArgumentException('tpEvento deve ter 6 dígitos.');
        }

        $nSeq = str_pad((string) $std->nSeqEvento, 2, '0', STR_PAD_LEFT);
        $id = 'ID' . $tpEvento . $std->chNFAg . $nSeq;

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $dom->preserveWhiteSpace = false;

        $evento = $dom->createElement('eventoNFAg');
        $evento->setAttribute('xmlns', self::NS);
        $evento->setAttribute('versao', $this->version);

        $inf = $dom->createElement('infEvento');
        $inf->setAttribute('Id', $id);

        $this->appendText($dom, $inf, 'cOrgao', (string) $std->cOrgao);
        $this->appendText($dom, $inf, 'tpAmb', (string) $std->tpAmb);
        $this->appendText($dom, $inf, 'CNPJ', (string) $std->CNPJ);
        $this->appendText($dom, $inf, 'chNFAg', (string) $std->chNFAg);
        $this->appendText($dom, $inf, 'dhEvento', (string) $std->dhEvento);
        $this->appendText($dom, $inf, 'tpEvento', $tpEvento);
        $this->appendText($dom, $inf, 'nSeqEvento', (string) ((int) $std->nSeqEvento));

        $det = $dom->createElement('detEvento');
        $det->setAttribute('versaoEvento', $this->version);
        // detEvento aceita um único elemento (xs:any). Envelopa em $wrapper (ex: evCancNFAg).
        $wrap = $dom->createElement($wrapper);
        foreach ($detChildren as $name => $value) {
            $this->appendText($dom, $wrap, (string) $name, $value);
        }
        $det->appendChild($wrap);
        $inf->appendChild($det);

        if (! empty($std->CNPJPAA)) {
            $infPAA = $dom->createElement('infPAA');
            $this->appendText($dom, $infPAA, 'CNPJPAA', (string) $std->CNPJPAA);
            $inf->appendChild($infPAA);
        }

        $evento->appendChild($inf);
        $dom->appendChild($evento);
        return (string) $dom->saveXML($dom->documentElement);
    }

    private function appendText(DOMDocument $dom, DOMElement $parent, string $name, mixed $value): void
    {
        if ($value === null || $value === '') return;
        $parent->appendChild($dom->createElement($name, htmlspecialchars((string) $value, ENT_XML1)));
    }
}
