<?php

use function Pest\Laravel\get;

test('homepage marketing experience is rendered', function () {
    get('/')
        ->assertOk()
        ->assertSeeText('Operational control for licenses, devices, packages, and logs.')
        ->assertSeeText('Signal Board')
        ->assertSeeText('Four surfaces. One command floor.')
        ->assertSeeText('Public homepage · cinematic operational landing page')
        ->assertSee('x-data="landingSignalBoard()"', false)
        ->assertDontSee('document.addEventListener(\'alpine:init\'', false);
});
