<?php

declare(strict_types=1);
use He4rt\Identity\Users\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->withoutVite();
    artisan('sync:permissions');
});

test('authenticated user can view documentation page', function (): void {
    actingAs(User::factory()->admin()->create());

    get('/admin/docs/introduction')
        ->assertSuccessful();
});

test('unauthenticated user cannot view documentation page', function (): void {
    actingAs(User::factory()->user()->create());

    get('/admin/docs/introduction')
        ->assertForbidden();
});
