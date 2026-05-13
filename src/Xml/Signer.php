<?php

declare(strict_types=1);

namespace Lna\Sped\Nfag\Xml;

use Lna\Sped\Nfag\Certificate\Certificate;
use RuntimeException;

final class Signer
{
    public const XMLDSIG = 'http://www.w3.org/2000/09/xmldsig#';
    public const C14N = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
    public const ENVELOPED = 'http://www.w3.org/2000/09/xmldsig#enveloped-signature';
    public const SHA1 = 'http://www.w3.org/2000/09/xmldsig#sha1';
    public const RSA_SHA1 = 'http://www.w3.org/2000/09/xmldsig#rsa-sha1';

    public function sign(string $xml, Certificate $certificate, string $tagName = 'infNFAg', string $idAttribute = 'Id'): string
    {
        $dom = Dom::load($xml);
        $node = $dom->getElementsByTagName($tagName)->item(0);
        if (! $node) throw new RuntimeException("Tag {$tagName} not found for signature.");
        $id = $node->attributes?->getNamedItem($idAttribute)?->nodeValue;
        if (! $id) throw new RuntimeException("Attribute {$idAttribute} not found in {$tagName}.");
        $node->setIdAttribute($idAttribute, true);
        $digestValue = base64_encode(sha1($node->C14N(false, false), true));
        $signature = $dom->createElementNS(self::XMLDSIG, 'Signature');
        $signedInfo = $dom->createElementNS(self::XMLDSIG, 'SignedInfo');
        $canonicalizationMethod = $dom->createElementNS(self::XMLDSIG, 'CanonicalizationMethod');
        $canonicalizationMethod->setAttribute('Algorithm', self::C14N);
        $signatureMethod = $dom->createElementNS(self::XMLDSIG, 'SignatureMethod');
        $signatureMethod->setAttribute('Algorithm', self::RSA_SHA1);
        $reference = $dom->createElementNS(self::XMLDSIG, 'Reference');
        $reference->setAttribute('URI', '#'.$id);
        $transforms = $dom->createElementNS(self::XMLDSIG, 'Transforms');
        foreach ([self::ENVELOPED, self::C14N] as $algorithm) { $t = $dom->createElementNS(self::XMLDSIG, 'Transform'); $t->setAttribute('Algorithm', $algorithm); $transforms->appendChild($t); }
        $digestMethod = $dom->createElementNS(self::XMLDSIG, 'DigestMethod'); $digestMethod->setAttribute('Algorithm', self::SHA1);
        $reference->appendChild($transforms); $reference->appendChild($digestMethod); $reference->appendChild($dom->createElementNS(self::XMLDSIG, 'DigestValue', $digestValue));
        $signedInfo->appendChild($canonicalizationMethod); $signedInfo->appendChild($signatureMethod); $signedInfo->appendChild($reference);
        $privateKey = openssl_pkey_get_private($certificate->privateKeyPem());
        if (! $privateKey) throw new RuntimeException('Unable to load private key.');
        $signatureValue = ''; openssl_sign($signedInfo->C14N(false, false), $signatureValue, $privateKey, OPENSSL_ALGO_SHA1);
        $signature->appendChild($signedInfo);
        $signature->appendChild($dom->createElementNS(self::XMLDSIG, 'SignatureValue', base64_encode($signatureValue)));
        $keyInfo = $dom->createElementNS(self::XMLDSIG, 'KeyInfo');
        $x509Data = $dom->createElementNS(self::XMLDSIG, 'X509Data');
        $x509Data->appendChild($dom->createElementNS(self::XMLDSIG, 'X509Certificate', $certificate->publicCertClean()));
        $keyInfo->appendChild($x509Data); $signature->appendChild($keyInfo);
        $node->parentNode?->appendChild($signature);
        return Dom::save($dom);
    }
}
