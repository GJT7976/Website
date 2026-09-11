<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['group', 'key', 'value'])]
class Setting extends Model
{
    /**
     * Read a single setting value by key, with a fallback default.
     * Cached for the request lifecycle worth of DB round-trips across a
     * page render; the tag is flushed whenever a setting is written.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            return static::where('key', $key)->value('value') ?? $default;
        });
    }

    /**
     * Write (create or update) a setting value and bust its cache entry.
     */
    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        Cache::forget("setting:{$key}");
    }

    /**
     * Read every setting in a group as a [key => value] array — convenient
     * for populating an admin settings form.
     */
    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->all();
    }
}
