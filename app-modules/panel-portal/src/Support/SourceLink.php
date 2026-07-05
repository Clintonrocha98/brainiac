<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Entry;

/**
 * Backlink "ver na fonte" dos espelhos: {repo_url}/blob/{default_branch}/{git_pointer}.
 * Null quando qualquer parte falta (nativo, sem git_pointer, projeto sem repo_url).
 */
final class SourceLink
{
    public static function for(Entry $entry): ?string
    {
        if ($entry->origin !== Origin::Mirror) {
            return null;
        }

        $project = $entry->originProject;
        $pointer = $entry->document?->git_pointer;

        if ($project?->repo_url === null || $pointer === null) {
            return null;
        }

        return sprintf(
            '%s/blob/%s/%s',
            mb_rtrim($project->repo_url, '/'),
            $project->default_branch ?? 'main',
            mb_ltrim($pointer, '/'),
        );
    }
}
