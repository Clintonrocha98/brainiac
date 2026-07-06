<?php

declare(strict_types=1);

namespace He4rt\Portal\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use He4rt\Catalog\Models\Project;
use He4rt\Portal\Support\CardChip;
use He4rt\Portal\Support\ContextCard;

final class ProjectsIndex extends ContextIndexPage
{
    protected static ?string $slug = 'projects';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('panel-portal::portal.nav.projects');
    }

    public function getTitle(): string
    {
        return __('panel-portal::portal.index.project.title');
    }

    public function getSubheading(): string
    {
        return __('panel-portal::portal.index.project.subtitle');
    }

    /**
     * @return array<int, ContextCard>
     */
    protected function cards(): array
    {
        return Project::query()
            ->withCount('taggedEntries')
            ->orderBy('business_name')
            ->get()
            ->map(static fn (Project $project): ContextCard => new ContextCard(
                badge: $project->acronym,
                title: $project->business_name,
                description: $project->technical_name,
                meta: __('panel-portal::portal.index.project.meta', ['count' => $project->tagged_entries_count]),
                url: ShowProject::getUrl(['project' => $project]),
                chips: [
                    $project->last_synced_at !== null
                        ? new CardChip(__('panel-portal::portal.index.project.chip_federation'), 'info')
                        : new CardChip(__('panel-portal::portal.index.project.chip_native'), 'primary'),
                ],
            ))
            ->all();
    }
}
