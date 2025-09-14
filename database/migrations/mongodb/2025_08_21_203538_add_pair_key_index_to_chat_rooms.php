<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create a unique index on pair_key
        DB::connection('mongodb')
            ->getMongoDB()
            ->selectCollection('chat_rooms')
            ->createIndex(['pair_key' => 1], ['unique' => true]);
    }

    public function down(): void
    {
        // Drop the index if it exists. Default name for { pair_key: 1 } is "pair_key_1".
        try {
            DB::connection('mongodb')
                ->getMongoDB()
                ->selectCollection('chat_rooms')
                ->dropIndex('pair_key_1');
        } catch (\Throwable $e) {
            // ignore if it doesn't exist
        }
    }
};
