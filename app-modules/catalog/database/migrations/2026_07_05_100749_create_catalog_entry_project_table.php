<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_entry_project', static function (Blueprint $table): void {
            $table->foreignUuid('entry_id')->constrained('catalog_entries')->cascadeOnDelete();
            $table->foreignUuid('project_id')->constrained('catalog_projects')->cascadeOnDelete();
            $table->primary(['entry_id', 'project_id']);
        });
    }
};
