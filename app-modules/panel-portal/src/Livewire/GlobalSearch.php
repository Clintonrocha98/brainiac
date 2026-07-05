<?php

declare(strict_types=1);

namespace He4rt\Portal\Livewire;

use He4rt\Catalog\Models\Entry;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Busca global do topo: título, resumo, id qualificado e palavras-chave.
 */
final class GlobalSearch extends Component
{
    public string $q = '';

    public function render(): View
    {
        $term = mb_trim($this->q);

        $results = $term === '' ? collect() : Entry::query()
            ->where(static function (Builder $query) use ($term): void {
                $like = '%'.$term.'%';

                $query
                    ->where('title', 'ilike', $like)
                    ->orWhere('summary', 'ilike', $like)
                    ->orWhere('qualified_id', 'ilike', $like)
                    ->orWhereRaw('keywords::text ilike ?', [$like]);
            })
            ->with('projects')
            ->orderBy('title')
            ->limit(6)
            ->get()
            ->map(static function (Entry $entry): array {
                $firstProject = $entry->projects->first();

                return [
                    'title' => $entry->title,
                    'qid' => $entry->qualified_id,
                    'format' => $entry->format,
                    'hint' => $firstProject !== null ? $firstProject->acronym : $entry->department->getLabel(),
                    'url' => $firstProject !== null
                        ? route('portal.projects.entry', ['project' => $firstProject, 'entry' => $entry])
                        : route('portal.areas.entry', ['area' => $entry->department->value, 'entry' => $entry]),
                ];
            })
            ->toBase();

        return view('panel-portal::livewire.global-search', [
            'results' => $results,
        ]);
    }
}
