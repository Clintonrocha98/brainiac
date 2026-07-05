<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_collection_entry', static function (Blueprint $table): void {
            $table->foreignUuid('collection_id')->constrained('catalog_collections')->cascadeOnDelete();
            $table->foreignUuid('entry_id')->constrained('catalog_entries')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['collection_id', 'entry_id']);
        });
    }
};
