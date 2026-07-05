<?php

declare(strict_types=1);

namespace He4rt\Catalog\Federation;

use He4rt\Catalog\DTOs\Snapshot;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Enums\Status;
use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\Project;
use He4rt\Identity\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $systemOwnerId = $this->systemOwnerId();

        DB::transaction(function () use ($project, $snapshot, $systemOwnerId): void {
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
                        'owner_id' => $existing !== null ? $existing->owner_id : $systemOwnerId,
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

            $project->update(['last_synced_at' => now()]);
        });
    }

    /**
     * Espelhos sem dono declarado pertencem a um usuário-sistema dedicado.
     */
    private function systemOwnerId(): string
    {
        return User::query()->firstOrCreate(
            ['email' => 'federation@brainiac.system'],
            ['name' => 'Federação', 'password' => Hash::make(Str::random(40))],
        )->id;
    }
}
