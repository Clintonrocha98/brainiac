<?php

declare(strict_types=1);

test('that true is true')->expect(true)->toBeTrue();

test('that false is false', function (): void {
    expect(value: false)->toBeFalse();
});
