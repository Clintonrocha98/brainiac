<?php

declare(strict_types=1);

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Enums\Audience;
use He4rt\Catalog\Enums\Format;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Purpose;
use He4rt\Catalog\Enums\Status;

test('purpose has the three canonical values', function (): void {
    expect(array_column(Purpose::cases(), 'value'))
        ->toBe(['reference', 'how-to', 'explanation']);
});

test('format carries the how-to value with a hyphen', function (): void {
    expect(Format::HowTo->value)->toBe('how-to');
});

test('origin distinguishes native from mirror', function (): void {
    expect(array_column(Origin::cases(), 'value'))->toBe(['native', 'mirror']);
});

test('audience is a superset of area plus all and external', function (): void {
    $audience = array_column(Audience::cases(), 'value');

    foreach (Area::cases() as $area) {
        expect($audience)->toContain($area->value);
    }
    expect($audience)->toContain('all')->toContain('external');
});

test('label is resolved through translation', function (): void {
    app()->setLocale('pt_BR');
    expect(Status::Published->getLabel())->toBe('Publicado');
});
