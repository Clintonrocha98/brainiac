<?php

declare(strict_types=1);

test('catalog service provider is registered', function (): void {
    expect(app()->getProviders(He4rt\Catalog\CatalogServiceProvider::class))
        ->not->toBeEmpty();
});
