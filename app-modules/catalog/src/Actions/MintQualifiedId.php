<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\Enums\Area;
use He4rt\Catalog\Models\Project;
use Illuminate\Support\Str;

final class MintQualifiedId
{
    /**
     * Cunha o id qualificado: PREFIXO:native_id, onde o prefixo é a sigla do
     * projeto de origem ou, na ausência dele, o nome da Área (departamento).
     */
    public function execute(?Project $origin, Area $department, string $nativeId): string
    {
        $prefix = $origin !== null ? $origin->acronym : Str::upper($department->value);

        return "{$prefix}:{$nativeId}";
    }
}
