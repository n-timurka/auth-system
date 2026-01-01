<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'key',
        'value',
        'segment',
        'type',
        'description',
    ];

    /**
     * Get the value attribute with proper type casting
     *
     * @param string $value
     * @return mixed
     */
    public function getValueAttribute($value): mixed
    {
        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'array', 'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value attribute with proper encoding
     *
     * @param mixed $value
     * @return void
     */
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = is_array($value) || is_object($value)
            ? json_encode($value)
            : (string) $value;
    }

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return false;
        }

        $setting->value = $value;
        return $setting->save();
    }

    /**
     * Scope a query to only include settings of a given segment
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $segment
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSegment($query, string $segment)
    {
        return $query->where('segment', $segment);
    }

    /**
     * Get all settings grouped by segment
     *
     * @return array
     */
    public static function getAllGrouped(): array
    {
        return static::all()->groupBy('segment')->map(function ($settings) {
            return $settings->mapWithKeys(function ($setting) {
                return [
                    $setting->key => [
                        'value' => $setting->value,
                        'type' => $setting->type,
                        'description' => $setting->description,
                    ]
                ];
            });
        })->toArray();
    }
}
