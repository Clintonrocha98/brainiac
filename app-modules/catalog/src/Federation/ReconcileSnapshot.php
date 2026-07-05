<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation;

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use Illuminate\Support\Facades\DB;

final class ReconcileSnapshot
{
    /**
     * Snapshot COMPLETO de um projeto: upsert dos espelhos que vieram +
     * apaga os espelhos daquele projeto ausentes no snapshot. Escopo estrito
     * (project, origin=mirror): nunca toca nativos nem outros projetos.
     */
    public function execute(Snapshot $snapshot): void
    {
        $project = Project::query()->where('acronym', $snapshot->acronym)->firstOrFail();

        DB::transaction(static function () use ($project, $snapshot): void {
            $seen = [];

            foreach ($snapshot->entries as $item) {
                $existing = Entry::query()->where('qualified_id', $item->qualifiedId)->first();

                $entry = Entry::query()->updateOrCreate(
                    ['qualified_id' => $item->qualifiedId],
                    [
                        'native_id' => $item->nativeId,
                        'project_id' => $project->id,
                        'title' => $item->title,
                        'summary' => $item->summary,
                        'purpose' => $item->purpose,
                        'format' => $item->format,
                        'origin' => Origin::Mirror,
                        'department' => $item->department,
                        'audience' => $existing !== null ? $existing->audience->all() : [$item->department->value],
                        'status' => $existing !== null ? $existing->status : Status::Published,
                        // Espelho não tem dono no Brainiac: o responsável vive no repo de origem.
                        'owner_id' => $existing?->owner_id,
                        // Provência do espelho: handles do git de quem criou/editou (fonte = repo).
                        'authors' => $item->authors,
                    ],
                );

                $entry->document()->updateOrCreate([], [
                    'body_markdown' => $item->bodyMarkdown,
                    'git_pointer' => $item->gitPointer,
                ]);

                $seen[] = $item->qualifiedId;
            }

            Entry::query()
                ->where('project_id', $project->id)
                ->where('origin', Origin::Mirror)
                ->whereNotIn('qualified_id', $seen)
                ->get()
                ->each->delete();

            $project->update([
                'repo_url' => $snapshot->repoUrl ?? $project->repo_url,
                'default_branch' => $snapshot->defaultBranch ?? $project->default_branch,
                'last_synced_at' => now(),
            ]);
        });
    }
}
