<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\Enums\PrdVersionState;
use He4rt\Catalog\Models\PrdVersion;

/**
 * Congela uma versão de PRD: o corpo vira imutável e a data de congelamento
 * passa a identificar a versão na pilha do leitor.
 */
final class FreezePrdVersion
{
    public function execute(PrdVersion $version): PrdVersion
    {
        $version->update([
            'state' => PrdVersionState::Frozen,
            'frozen_at' => now(),
        ]);

        return $version;
    }
}
