<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_projects', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('business_name');
            $table->string('technical_name');
            $table->string('slug')->unique();
            $table->string('acronym')->unique();
            $table->string('repo_url')->nullable();         // URL do repo (para link à fonte)
            $table->string('default_branch')->nullable();   // branch default (compõe o link)
            $table->string('webhook_token')->nullable();   // hash
            $table->text('hmac_secret')->nullable();        // cifrado (cast encrypted)
            $table->timestampTz('last_synced_at')->nullable();
            $table->timestampsTz();
        });
    }
};
