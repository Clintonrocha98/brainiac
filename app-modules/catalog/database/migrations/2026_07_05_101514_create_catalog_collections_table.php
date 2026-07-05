<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_collections', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('summary');
            $table->text('body_markdown')->nullable();
            $table->jsonb('audience');
            $table->foreignUuid('owner_id')->constrained('identity_users')->restrictOnDelete();
            $table->string('status');
            $table->boolean('has_image')->default(false);
            $table->boolean('has_mermaid')->default(false);
            $table->boolean('has_artifact')->default(false);
            $table->jsonb('mentions')->nullable();
            $table->timestampsTz();
        });
    }
};
