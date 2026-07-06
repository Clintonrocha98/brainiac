<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use He4rt\Catalog\Models\Entry;
use He4rt\Portal\Filament\Pages\ShowAreaEntry;
use He4rt\Portal\Filament\Pages\ShowProjectEntry;

/**
 * URL canônica de leitura de uma Entrada fora de um contexto conhecido:
 * primeiro projeto-assunto ou, na ausência, a área dona.
 */
final class EntryUrl
{
    public static function preferred(Entry $entry): string
    {
        $subjectProject = $entry->projects->first();

        if ($subjectProject !== null) {
            return ShowProjectEntry::getUrl(['project' => $subjectProject, 'entry' => $entry]);
        }

        return ShowAreaEntry::getUrl(['area' => $entry->department, 'entry' => $entry]);
    }
}
