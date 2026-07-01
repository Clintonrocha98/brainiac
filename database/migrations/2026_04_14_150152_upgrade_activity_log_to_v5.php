<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', static function (Blueprint $table): void {
            $table->json('attribute_changes')->nullable()->after('causer_id');
        });

        DB::table('activity_log')
            ->whereNotNull('properties')
            ->orderBy('id')
            ->chunkById(1_000, static function ($rows): void {
                foreach ($rows as $row) {
                    $properties = json_decode((string) $row->properties, associative: true) ?? [];
                    $changes = array_intersect_key($properties, array_flip(['attributes', 'old']));
                    $remaining = array_diff_key($properties, array_flip(['attributes', 'old']));

                    DB::table('activity_log')->where('id', $row->id)->update([
                        'attribute_changes' => $changes === [] ? null : json_encode($changes),
                        'properties' => $remaining === [] ? null : json_encode($remaining),
                    ]);
                }
            });

        Schema::table('activity_log', static function (Blueprint $table): void {
            $table->dropColumn('batch_uuid');
        });
    }

    public function down(): void
    {
        // Forward-only upgrade; intentionally a no-op per project decision.
    }
};
