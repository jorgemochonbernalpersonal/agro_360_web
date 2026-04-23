<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = ['key', 'value'];

    // ── Static helpers ───────────────────────────────────────────────────────

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting:{$key}", 300, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            return $row ? $row->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("app_setting:{$key}");
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key);
        return $value === null ? $default : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
