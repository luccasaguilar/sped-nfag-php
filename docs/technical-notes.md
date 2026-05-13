# Technical notes

## NFAg processing model

The NFAg recepção service is designed as a synchronous authorization flow. This project therefore exposes `sefazRecepcao()` directly, without `nRec` / receipt polling.

## SOAP operation names

The operation names used here follow the official service names. If the final WSDL changes wrapper or namespace, adjust `src/Soap/SoapEnvelope.php` and `src/Tools.php`.

## Signature

The signer uses the common DFe enveloped XMLDSig pattern. Validate against the final NFAg MOC and schemas.
