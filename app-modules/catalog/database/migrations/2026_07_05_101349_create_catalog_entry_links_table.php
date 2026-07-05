<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_entry_links', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('from_entry_id')->constrained('catalog_entries')->cascadeOnDelete();
            $table->foreignUuid('to_entry_id')->constrained('catalog_entries')->cascadeOnDelete();
            $table->string('type');
            $table->timestampsTz();

            $table->unique(['from_entry_id', 'to_entry_id', 'type']);
        });
    }
};
