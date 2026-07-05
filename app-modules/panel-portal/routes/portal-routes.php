<?php

declare(strict_types=1);

use He4rt\Portal\Livewire\AreasIndex;
use He4rt\Portal\Livewire\CollectionsIndex;
use He4rt\Portal\Livewire\ProjectsIndex;
use He4rt\Portal\Livewire\ShowContext;
use He4rt\Portal\Livewire\ShowEntry;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('portal')->name('portal.')->group(static function (): void {
    Route::redirect('/', '/portal/projects')->name('home');

    Route::get('/projects', ProjectsIndex::class)->name('projects.index');
    Route::get('/areas', AreasIndex::class)->name('areas.index');
    Route::get('/collections', CollectionsIndex::class)->name('collections.index');

    Route::get('/projects/{project:slug}', ShowContext::class)->name('projects.show');
    Route::get('/projects/{project:slug}/e/{entry}', ShowEntry::class)->name('projects.entry');

    Route::get('/areas/{area}', ShowContext::class)->name('areas.show');
    Route::get('/areas/{area}/e/{entry}', ShowEntry::class)->name('areas.entry');

    Route::get('/collections/{collection:slug}', ShowContext::class)->name('collections.show');
    Route::get('/collections/{collection:slug}/e/{entry}', ShowEntry::class)->name('collections.entry');
});
