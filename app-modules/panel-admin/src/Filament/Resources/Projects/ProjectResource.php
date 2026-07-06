<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Projects;

use App\Enums\NavigationGroup;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Projects\Pages\CreateProject;
use He4rt\Admin\Filament\Resources\Projects\Pages\EditProject;
use He4rt\Admin\Filament\Resources\Projects\Pages\ListProjects;
use He4rt\Admin\Filament\Resources\Projects\Schemas\ProjectForm;
use He4rt\Admin\Filament\Resources\Projects\Tables\ProjectsTable;
use He4rt\Catalog\Models\Project;
use UnitEnum;

/**
 * Administração de Projetos e da configuração de federação. As credenciais
 * (webhook token e segredo HMAC) NUNCA são exibidas — apenas rotacionadas,
 * com revelação única dos novos valores.
 */
class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $slug = 'catalog/projects';

    protected static ?string $recordTitleAttribute = 'business_name';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Catalog;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::table($table);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['business_name', 'technical_name', 'acronym'];
    }

    public static function getModelLabel(): string
    {
        return __('panel-admin::catalog.projects.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('panel-admin::catalog.projects.plural');
    }
}
