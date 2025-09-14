<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

class ChatRoom extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'chat_rooms';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = true;

   protected $fillable = ['pair_key','participant_ids','last_message_at'];
    protected $casts = [
        'participant_ids' => 'array',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Defensive Attribute to ensure participant_ids is always a plain array of strings.
     * - READ: if already array -> return; if JSON string -> decode; otherwise wrap.
     * - WRITE: always store array<string>; sorted for stability.
     */
    protected function participantIds(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    $t = ltrim($value);
                    if ($t !== '' && ($t[0] === '[' || $t[0] === '{')) {
                        $decoded = json_decode($value, true);
                        if (is_array($decoded)) {
                            return array_map('strval', $decoded);
                        }
                    }
                    return [$value];
                }
                return array_map('strval', (array) $value);
            },
            set: function ($value) {
                $arr = is_array($value) ? $value : [$value];
                $arr = array_map('strval', $arr);
                sort($arr);
                return $arr;
            }
        );
    }

    /**
     * Build a deterministic key for a 1:1 room (sorted pair of user IDs).
     */
    public static function makePairKey(string $a, string $b): string
    {
        $a = (string) $a; $b = (string) $b;
        [$x, $y] = $a < $b ? [$a, $b] : [$b, $a];
        return "$x:$y";
    }

    /**
     * Create-or-return a 1:1 room between two users (race-safe upsert on pair_key).
     */
    public static function firstOrCreateBetween(string $a, string $b): self
    {
        $a = (string) $a; $b = (string) $b;
        [$x, $y] = $a < $b ? [$a, $b] : [$b, $a];
        $pair = "$x:$y";

        return static::updateOrCreate(
            ['pair_key' => $pair],
            ['participant_ids' => [$x, $y], 'last_message_at' => now()]
        );
    }


    /**
     * Find an existing 1:1 room by users (pair_key preferred; fallback for legacy docs).
     */
    public static function between(string $a, string $b): ?self
    {
        $pair = static::makePairKey($a, $b);

        return static::where('pair_key', $pair)
            ->orWhere(function ($q) use ($a, $b) {
                $q->where('participant_ids', 'all', [(string) $a, (string) $b]);
            })
            ->first();
    }

    /**
     * Helper: tolerant find by _id (accepts 24-hex string or raw string).
     */
    public static function findTolerant(string $id): ?self
    {
        if (preg_match('/^[0-9a-f]{24}$/i', $id)) {
            $obj = new ObjectId($id);
            return static::where('_id', $obj)->first() ?? static::where('_id', $id)->first();
        }
        return static::where('_id', $id)->first();
    }
}
