<?php

declare(strict_types=1);

use He4rt\Catalog\Actions\DeriveBodyFacts;

test('detects image, mermaid and mentions in the body', function (): void {
    $markdown = <<<'MD'
    # Título
    ![diagrama](foto.png)
    ```mermaid
    graph TD; A-->B;
    ```
    Veja [o módulo](RPQ:pagamentos/reference/modulo) e o arquivo repo://docs/x.md.
    MD;

    $facts = app(DeriveBodyFacts::class)->execute($markdown);

    expect($facts->hasImage)->toBeTrue()
        ->and($facts->hasMermaid)->toBeTrue()
        ->and($facts->mentions)->toContain('RPQ:pagamentos/reference/modulo');
});

test('empty body yields all-false facts', function (): void {
    $facts = app(DeriveBodyFacts::class)->execute('texto simples');

    expect($facts->hasImage)->toBeFalse()
        ->and($facts->hasMermaid)->toBeFalse()
        ->and($facts->hasArtifact)->toBeFalse()
        ->and($facts->mentions)->toBe([]);
});
