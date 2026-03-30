<?php

use App\Models\License;

use function Pest\Laravel\actingAs;

it('licenses index keeps a single shared view action per row', function () {
    $admin = createAdmin();
    License::factory()->create();

    actingAs($admin)
        ->get(route('licenses.index'))
        ->assertSuccessful()
        ->assertSee('table-action table-action--primary', false)
        ->assertSee('aria-label="License row actions"', false)
        ->assertDontSee('table-action table-action--danger', false)
        ->assertDontSee('onsubmit="return confirm', false);
});
