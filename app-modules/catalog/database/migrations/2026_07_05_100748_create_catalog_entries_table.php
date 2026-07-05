<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_entries', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('qualified_id')->unique();
            $table->string('native_id');
            $table->foreignUuid('project_id')->nullable()->constrained('catalog_projects')->nullOnDelete();
            $table->string('slug')->nullable();
            $table->string('title');
            $table->text('summary');
            $table->string('purpose');
            $table->string('format');
            $table->string('origin');
            $table->string('department');
            $table->jsonb('audience');
            $table->jsonb('keywords')->nullable();
            $table->string('status');
            $table->foreignUuid('owner_id')->constrained('identity_users')->restrictOnDelete();
            $table->timestampsTz();

            $table->index(['project_id', 'origin']); // reconciliação da federação
        });
    }
};
