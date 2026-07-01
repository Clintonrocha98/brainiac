<?php

declare(strict_types=1);

use He4rt\Identity\Users\User;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

it('logs changes into the v5 attribute_changes column when a user is updated', function (): void {
    $user = User::factory()->create(['name' => 'Before']);

    actingAs($user);

    $user->update(['name' => 'After']);

    $activity = Activity::query()
        ->whereMorphedTo('subject', $user)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($activity->attribute_changes)->not->toBeNull()
        ->and($activity->attribute_changes->get('attributes')['name'] ?? null)->toBe('After')
        ->and($activity->attribute_changes->get('old')['name'] ?? null)->toBe('Before')
        ->and($activity->properties->toArray())->not->toHaveKey('attributes')
        ->and($activity->properties->toArray())->not->toHaveKey('old');
});

it('captures ip_address and user_agent via beforeActivityLogged()', function (): void {
    $user = User::factory()->create();

    actingAs($user);

    $user->update(['name' => 'Renamed']);

    $activity = Activity::query()
        ->whereMorphedTo('subject', $user)
        ->latest('id')
        ->firstOrFail();

    expect($activity->properties->toArray())
        ->toHaveKeys(['ip_address', 'user_agent']);
});

it('records the authenticated user as causer via HasActivity', function (): void {
    $actor = User::factory()->create();
    $subject = User::factory()->create();

    actingAs($actor);

    $subject->update(['name' => 'changed']);

    expect($actor->activitiesAsCauser()->exists())->toBeTrue();
});
