<?php

declare(strict_types=1);

namespace He4rt\Portal\Livewire;

use He4rt\Catalog\Models\Project;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('panel-portal::layouts.portal')]
final class ProjectsIndex extends Component
{
    public function render(): View
    {
        $cards = Project::query()
            ->withCount('taggedEntries')
            ->orderBy('business_name')
            ->get()
            ->map(static fn (Project $project): array => [
                'badge' => $project->acronym,
                'title' => $project->business_name,
                'description' => $project->technical_name,
                'meta' => __('panel-portal::portal.index.project.meta', ['count' => $project->tagged_entries_count]),
                'chips' => [[
                    'label' => $project->last_synced_at !== null
                        ? __('panel-portal::portal.index.project.chip_federation')
                        : __('panel-portal::portal.index.project.chip_native'),
                    'style' => $project->last_synced_at !== null ? 'mirror' : 'accent',
                ]],
                'url' => route('portal.projects.show', ['project' => $project]),
            ])
            ->all();

        return view('panel-portal::livewire.context-index', [
            'title' => __('panel-portal::portal.index.project.title'),
            'subtitle' => __('panel-portal::portal.index.project.subtitle'),
            'cards' => $cards,
        ])->title(__('panel-portal::portal.index.project.title').' · Brainiac');
    }
}
