<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_prd_versions', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('entry_id')->constrained('catalog_entries')->cascadeOnDelete();
            $table->unsignedInteger('major')->nullable();
            $table->unsignedInteger('minor')->nullable();
            $table->text('body_markdown');
            $table->string('state')->default('draft');
            $table->timestampTz('frozen_at')->nullable();
            $table->boolean('has_image')->default(value: false);
            $table->boolean('has_mermaid')->default(value: false);
            $table->boolean('has_artifact')->default(value: false);
            $table->jsonb('mentions')->nullable();
            $table->timestampsTz();
        });
    }
};
