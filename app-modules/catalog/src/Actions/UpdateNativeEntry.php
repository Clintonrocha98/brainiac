<?php

declare(strict_types=1);

namespace He4rt\Catalog\Actions;

use He4rt\Catalog\DTOs\NativeEntryData;
use He4rt\Catalog\Models\Entry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Atualiza uma Entrada nativa, recunhando o id qualificado quando o
 * departamento ou o native_id mudam. Corpo vazio remove o Documento.
 */
final readonly class UpdateNativeEntry
{
    public function __construct(private MintQualifiedId $mintQualifiedId) {}

    public function execute(Entry $entry, NativeEntryData $data): Entry
    {
        return DB::transaction(function () use ($entry, $data): Entry {
            $entry->update([
                'qualified_id' => $this->mintQualifiedId->execute(origin: null, department: $data->department, nativeId: $data->nativeId),
                'native_id' => $data->nativeId,
                'slug' => Str::slug($data->title),
                'title' => $data->title,
                'summary' => $data->summary,
                'purpose' => $data->purpose,
                'format' => $data->format,
                'department' => $data->department,
                'audience' => $data->audience,
                'keywords' => $data->keywords,
                'status' => $data->status,
                'owner_id' => $data->ownerId,
            ]);

            if ($data->bodyMarkdown !== null) {
                $entry->document()->updateOrCreate([], ['body_markdown' => $data->bodyMarkdown]);
            } else {
                $entry->document()->delete();
            }

            $entry->projects()->sync($data->subjectProjectIds);

            return $entry->refresh();
        });
    }
}
