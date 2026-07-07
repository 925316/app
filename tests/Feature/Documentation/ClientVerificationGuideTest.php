<?php

it('requires the cpp client verification guide to exist at the repository root', function () {
    expect(base_path('CPP_CLIENT_VERIFICATION.md'))->toBeFile();
});

it('requires the cpp client verification guide to document the current client verification contract', function () {
    $guide = file_get_contents(base_path('CPP_CLIENT_VERIFICATION.md'));

    expect($guide)->toBeString();

    expect($guide)
        ->toContain('Purpose')
        ->toContain('Endpoint Contract')
        ->toContain('Request Fields')
        ->toContain('Current Response Envelope')
        ->toContain('Current Signed Payload Scope')
        ->toContain('Canonical JSON')
        ->toContain('OpenSSL')
        ->toContain('RSA-SHA256')
        ->toContain('Success Example')
        ->toContain('Failure Example')
        ->toContain('Nonce and Timestamp Behavior')
        ->toContain('Update Check Query Behavior')
        ->toContain('Limitations')
        ->toContain('This repository does not ship an official C++ SDK');

    expect($guide)
        ->toContain('POST /api/account/login')
        ->toContain('POST /api/license/check')
        ->toContain('POST /api/license/activate')
        ->toContain('POST /api/license/unbind')
        ->toContain('GET /api/update/check')
        ->toContain('nonce')
        ->toContain('timestamp')
        ->toContain('session_token')
        ->toContain('release_channel')
        ->toContain('current_version');

    expect($guide)
        ->toContain('signature')
        ->toContain('meta.signature.algorithm')
        ->toContain('meta.signature.key_id')
        ->toContain('code')
        ->toContain('error_code')
        ->toContain('message')
        ->toContain('data');

    expect($guide)
        ->toContain('Successful responses sign the `data` payload.')
        ->toContain('Signed controller error responses sign `data` when it is `null`.')
        ->toContain('Validation error responses sign `data` when it is `null`.')
        ->toContain('Do not treat unsigned envelope fields as integrity-protected.')
        ->not->toContain('meta.signature.covers');

    expect($guide)
        ->toContain('canonical JSON')
        ->toContain('sorted by key')
        ->toContain('JSON_UNESCAPED_UNICODE')
        ->toContain('JSON_UNESCAPED_SLASHES')
        ->toContain('base64')
        ->toContain('openssl_sign')
        ->toContain('OPENSSL_ALGO_SHA256');

    expect($guide)
        ->toContain('Nonce replay')
        ->toContain('Timestamp out of window')
        ->toContain('300')
        ->toContain('GET /api/update/check does not use nonce or timestamp request fields.')
        ->toContain('release_channel defaults to stable')
        ->toContain('release_channel is normalized to lowercase')
        ->toContain('session_token is trimmed')
        ->toContain('current_version is trimmed');
});
