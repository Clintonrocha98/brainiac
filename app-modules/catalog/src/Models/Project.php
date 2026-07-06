<?php

declare(strict_types=1);

namespace He4rt\Catalog\Models;

use App\Models\BaseModel;
use He4rt\Catalog\Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $business_name
 * @property string $technical_name
 * @property string $slug
 * @property string $acronym
 * @property string|null $repo_url
 * @property string|null $default_branch
 * @property string|null $webhook_token
 * @property string|null $hmac_secret
 * @property Carbon|null $last_synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Entry> $entries
 * @property-read Collection<int, Entry> $taggedEntries
 *
 * @extends BaseModel<ProjectFactory>
 */
#[UseFactory(ProjectFactory::class)]
#[Table(name: 'catalog_projects')]
final class Project extends BaseModel
{
    /**
     * Credenciais da federação nunca saem em serialização (snapshots do
     * Livewire, JSON, activity log). Acesso somente via propriedade direta.
     *
     * @var list<string>
     */
    protected $hidden = [
        'webhook_token',
        'hmac_secret',
    ];

    /**
     * Entradas cuja ORIGEM (project_id) é este projeto.
     *
     * @return HasMany<Entry, $this>
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'project_id');
    }

    /**
     * Entradas que este projeto é ASSUNTO (faceta, pivot).
     *
     * @return BelongsToMany<Entry, $this>
     */
    public function taggedEntries(): BelongsToMany
    {
        return $this->belongsToMany(Entry::class, 'catalog_entry_project');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hmac_secret' => 'encrypted',
            'last_synced_at' => 'datetime',
        ];
    }
}
