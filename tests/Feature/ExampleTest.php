<?php

declare(strict_types=1);

use App\Enums\FilamentPanel;
use Filament\Facades\Filament;

use function Pest\Laravel\get;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel(FilamentPanel::Admin->value));
});

test('the application returns a successful response', function (): void {
    get('/')
        ->assertRedirect(Filament::getLoginUrl());
});
