<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\DTOs\NativeEntryData;
use He4rt\Catalog\Enums\Origin;
use He4rt\Catalog\Models\Entry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cria uma Entrada nativa: cunha o id qualificado (sem projeto de origem, o
 * prefixo vem do departamento), grava o corpo como Documento e sincroniza a
 * faceta projeto (assunto).
 */
final readonly class CreateNativeEntry
{
    public function __construct(private MintQualifiedId $mintQualifiedId) {}

    public function execute(NativeEntryData $data): Entry
    {
        return DB::transaction(function () use ($data): Entry {
            $entry = Entry::query()->create([
                'qualified_id' => $this->mintQualifiedId->execute(origin: null, department: $data->department, nativeId: $data->nativeId),
                'native_id' => $data->nativeId,
                'project_id' => null,
                'slug' => Str::slug($data->title),
                'title' => $data->title,
                'summary' => $data->summary,
                'purpose' => $data->purpose,
                'format' => $data->format,
                'origin' => Origin::Native,
                'department' => $data->department,
                'audience' => $data->audience,
                'keywords' => $data->keywords,
                'status' => $data->status,
                'owner_id' => $data->ownerId,
                'authors' => null,
            ]);

            if ($data->bodyMarkdown !== null) {
                $entry->document()->create(['body_markdown' => $data->bodyMarkdown]);
            }

            $entry->projects()->sync($data->subjectProjectIds);

            return $entry;
        });
    }
}
