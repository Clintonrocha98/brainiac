<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Search;

use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\Contracts\GlobalSearchProvider;
use He4rt\Catalog\Models\Entry;
use He4rt\Portal\Support\EntryUrl;

/**
 * Busca global do portal: pesquisa Entradas por título, resumo, id
 * qualificado e palavras-chave, no lugar da busca por resources do Filament.
 */
final class EntryGlobalSearchProvider implements GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $term = mb_trim($query);

        if ($term === '') {
            return null;
        }

        $results = Entry::query()
            ->searching($term)
            ->with('projects')
            ->orderBy('title')
            ->limit(10)
            ->get()
            ->map(static fn (Entry $entry): GlobalSearchResult => new GlobalSearchResult(
                title: $entry->title,
                url: EntryUrl::preferred($entry),
                details: [
                    __('panel-portal::portal.search.detail_id') => $entry->qualified_id,
                    __('panel-portal::portal.search.detail_context') => $entry->projects->first()->acronym
                        ?? $entry->department->getLabel(),
                ],
            ));

        return GlobalSearchResults::make()
            ->category(__('panel-portal::portal.search.category'), $results);
    }
}
