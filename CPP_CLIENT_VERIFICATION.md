## Purpose

This guide explains how a C++ client can call the current Atelier OS API and verify signed responses with the contract that exists in this repository today.

This repository does not ship an official C++ SDK.

## Endpoint Contract

The current client-facing endpoints are:

- `POST /api/account/login`
- `POST /api/license/check`
- `POST /api/license/activate`
- `POST /api/license/unbind`
- `GET /api/update/check`

The POST endpoints accept JSON request bodies. The update endpoint uses query parameters.

## Request Fields

Common POST request fields:

- `hwid`
- `nonce`
- `timestamp`

Route-specific request fields:

- `POST /api/account/login`: `email`, `password`, `hwid`, `nonce`, `timestamp`, `version`, optional `country_code`
- `POST /api/license/check`: `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`, optional `version`
- `POST /api/license/activate`: `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`, optional `version`
- `POST /api/license/unbind`: `session_token`, `license_key`, `hwid`, `nonce`, `timestamp`, optional `version`
- `GET /api/update/check`: `session_token`, `release_channel`, `current_version`

Current normalization rules that matter to a client:

- `session_token is trimmed`
- `current_version is trimmed`
- `release_channel is normalized to lowercase`
- `release_channel defaults to stable`

## Current Response Envelope

The current response envelope includes these fields:

- `code`
- `error_code`
- `message`
- `data`
- `signature`
- `meta.signature.algorithm`
- `meta.signature.key_id`

Typical signed success envelope shape:

```json
{
  "code": 200,
  "error_code": null,
  "message": "OK",
  "data": {
    "status": "active",
    "license_key": "ABCDE-ABCDE-ABCDE-ABCDE",
    "expires_at": "2026-12-31 23:59:59"
  },
  "signature": "base64...",
  "meta": {
    "signature": {
      "algorithm": "RSA-2048-SHA256",
      "key_id": "main-2026-01"
    }
  }
}
```

Typical signed failure envelope shape:

```json
{
  "code": 422,
  "error_code": "VALIDATION_FAILED",
  "message": "Timestamp is required.",
  "data": null,
  "signature": "base64...",
  "meta": {
    "signature": {
      "algorithm": "RSA-2048-SHA256",
      "key_id": "main-2026-01"
    }
  }
}
```

## Current Signed Payload Scope

Successful responses sign the `data` payload.

Signed controller error responses sign `data` when it is `null`.

Validation error responses sign `data` when it is `null`.

Do not treat unsigned envelope fields as integrity-protected.

In other words, `code`, `error_code`, `message`, and the metadata are useful for control flow, but the current signature contract only protects the canonicalized `data` value that was passed into `signData()`.

## Canonical JSON

The signature is produced over canonical JSON, not over the raw HTTP body.

Current canonical JSON behavior:

- associative arrays are sorted by key
- nested associative arrays are also sorted by key
- list order is preserved
- encoding uses `JSON_UNESCAPED_UNICODE`
- encoding uses `JSON_UNESCAPED_SLASHES`
- the resulting signature is emitted as `base64`

For a C++ verifier, mirror the same canonical JSON rules before checking the signature.

Example canonical JSON input for a successful payload:

```json
{"expires_at":"2026-12-31 23:59:59","license_key":"ABCDE-ABCDE-ABCDE-ABCDE","status":"active"}
```

The keys are sorted by key before signing.

## OpenSSL

The server signs with `openssl_sign`.

## RSA-SHA256

The signing algorithm is RSA with SHA-256, implemented as `OPENSSL_ALGO_SHA256` in PHP and described in the response metadata as `RSA-2048-SHA256`.

High-level verification flow for a C++ client:

1. Parse the JSON response.
2. Extract `data`, `signature`, `meta.signature.algorithm`, and `meta.signature.key_id`.
3. Canonicalize only the `data` field with the same canonical JSON rules.
4. Base64-decode the `signature` value.
5. Verify the decoded signature against the canonical JSON bytes with the expected public key and RSA-SHA256.
6. Reject the response if signature verification fails.

Equivalent OpenSSL CLI verification steps:

```bash
openssl base64 -d -A -in signature.b64 -out signature.bin
openssl dgst -sha256 -verify public_key.pem -signature signature.bin payload.json
```

## Success Example

Example successful license check response:

```json
{
  "code": 200,
  "error_code": null,
  "message": "OK",
  "data": {
    "expires_at": "2026-12-31 23:59:59",
    "expires_timestamp": 1798761599,
    "plan_level": "pro",
    "status": "active",
    "username": "operator"
  },
  "signature": "base64...",
  "meta": {
    "signature": {
      "algorithm": "RSA-2048-SHA256",
      "key_id": "main-2026-01"
    }
  }
}
```

Verification target for this example: canonicalize the `data` object only, then verify the decoded `signature` bytes against that canonical JSON.

## Failure Example

Example validation failure response:

```json
{
  "code": 422,
  "error_code": "VALIDATION_FAILED",
  "message": "Nonce is required.",
  "data": null,
  "signature": "base64...",
  "meta": {
    "signature": {
      "algorithm": "RSA-2048-SHA256",
      "key_id": "main-2026-01"
    }
  }
}
```

Verification target for this example: canonicalize `null`, then verify the decoded signature against that canonical JSON value.

## Nonce and Timestamp Behavior

The POST endpoints use replay and freshness checks.

- `Nonce replay` is rejected.
- `Timestamp out of window` is rejected.
- The freshness window is `300` seconds.

Apply these rules to:

- `POST /api/account/login`
- `POST /api/license/check`
- `POST /api/license/activate`
- `POST /api/license/unbind`

`GET /api/update/check does not use nonce or timestamp request fields.`

## Update Check Query Behavior

`GET /api/update/check` accepts query parameters, not a JSON body.

Current behavior to mirror in a client:

- `session_token` is optional for request validation, but if present it is trimmed
- `release_channel` is optional for request validation
- `release_channel defaults to stable`
- `release_channel is normalized to lowercase`
- `current_version` is optional for request validation
- `current_version is trimmed`

Example query:

```text
GET /api/update/check?session_token=token123&release_channel=Stable&current_version=25.7.0
```

The normalized server-side values become `session_token=token123`, `release_channel=stable`, and `current_version=25.7.0`.

## Limitations

- This guide documents the current server contract only.
- This repository does not ship an official C++ SDK.
- Do not assume every top-level response field is signed.
- Do not treat unsigned envelope fields as integrity-protected.
- If you need a stronger contract, add a separate protocol-hardening task instead of assuming it already exists.
