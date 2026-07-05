<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_documents', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('entry_id')->unique()->constrained('catalog_entries')->cascadeOnDelete();
            $table->text('body_markdown');
            $table->string('git_pointer')->nullable();
            $table->boolean('has_image')->default(false);
            $table->boolean('has_mermaid')->default(false);
            $table->boolean('has_artifact')->default(false);
            $table->jsonb('mentions')->nullable();
            $table->timestampsTz();
        });
    }
};
