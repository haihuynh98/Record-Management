<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a system setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a system setting value by key
     */
    public static function setValue(string $key, $value, string $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Check if login is blocked
     */
    public static function isLoginBlocked(): bool
    {
        return (bool) static::getValue('login_blocked', false);
    }

    /**
     * Set login blocked status
     */
    public static function setLoginBlocked(bool $blocked): void
    {
        static::setValue(
            'login_blocked',
            $blocked ? '1' : '0',
            'Trạng thái chặn đăng nhập hệ thống'
        );
    }
}
