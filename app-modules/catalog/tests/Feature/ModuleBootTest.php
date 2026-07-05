<?php

declare(strict_types=1);

use He4rt\Catalog\CatalogServiceProvider;

test('catalog service provider is registered', function (): void {
    expect(app()->getProviders(CatalogServiceProvider::class))
        ->not->toBeEmpty();
});
