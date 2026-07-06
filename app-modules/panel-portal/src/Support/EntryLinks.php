<?php

declare(strict_types=1);

namespace He4rt\Portal\Support;

use He4rt\Catalog\Models\Entry;
use He4rt\Catalog\Models\EntryLink;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Monta as ligações tipadas de uma Entrada nas duas direções. O destino fica
 * no contexto atual quando pertence a ele; caso contrário, cai na URL
 * canônica da Entrada.
 */
final class EntryLinks
{
    /**
     * @param  EloquentCollection<int, Entry>  $contextEntries
     * @return array<int, EntryLinkItem>
     */
    public function for(Entry $entry, PortalContext $context, EloquentCollection $contextEntries): array
    {
        return EntryLink::query()
            ->where('from_entry_id', $entry->id)
            ->orWhere('to_entry_id', $entry->id)
            ->with(['fromEntry.projects', 'toEntry.projects'])
            ->get()
            ->map(static function (EntryLink $link) use ($entry, $context, $contextEntries): EntryLinkItem {
                $outbound = $link->from_entry_id === $entry->id;
                $target = $outbound ? $link->toEntry : $link->fromEntry;

                return new EntryLinkItem(
                    label: __('panel-portal::portal.links.'.$link->type->value.'.'.($outbound ? 'out' : 'in')),
                    title: $target->title,
                    qualifiedId: $target->qualified_id,
                    url: $contextEntries->contains('id', $target->id)
                        ? $context->entryUrl($target)
                        : EntryUrl::preferred($target),
                );
            })
            ->all();
    }
}
