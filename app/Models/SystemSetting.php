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

    /**
     * Get configured off-hours start time (HH:MM)
     */
    public static function getOffHoursStart(): ?string
    {
        return static::getValue('off_hours_start');
    }

    /**
     * Set off-hours start time (HH:MM)
     */
    public static function setOffHoursStart(string $time): void
    {
        static::setValue(
            'off_hours_start',
            $time,
            'Giờ bắt đầu ngoài giờ làm việc (HH:MM)'
        );
    }

    /**
     * Get configured off-hours end time (HH:MM)
     */
    public static function getOffHoursEnd(): ?string
    {
        return static::getValue('off_hours_end');
    }

    /**
     * Set off-hours end time (HH:MM)
     */
    public static function setOffHoursEnd(string $time): void
    {
        static::setValue(
            'off_hours_end',
            $time,
            'Giờ kết thúc ngoài giờ làm việc (HH:MM)'
        );
    }

    /**
     * Check if a datetime is within off-hours time range
     */
    public static function isWithinOffHours(\Carbon\Carbon $datetime): bool
    {
        $startTime = static::getOffHoursStart();
        $endTime = static::getOffHoursEnd();

        if (!$startTime || !$endTime) {
            return false;
        }

        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i');

        [$startHour, $startMinute] = explode(':', $startTime);
        [$endHour, $endMinute] = explode(':', $endTime);

        $startDateTime = \Carbon\Carbon::parse($date . ' ' . $startTime);
        $endDateTime = \Carbon\Carbon::parse($date . ' ' . $endTime);

        // Nếu thời gian kết thúc nhỏ hơn thời gian bắt đầu, có nghĩa là qua đêm
        if ($endTime < $startTime) {
            // Qua đêm: từ start đến 23:59:59 hôm nay và từ 00:00:00 đến end hôm sau
            $endOfDay = \Carbon\Carbon::parse($date . ' 23:59:59');
            $startOfNextDay = \Carbon\Carbon::parse($date . ' 00:00:00')->addDay();
            $endOfNextDay = \Carbon\Carbon::parse($date . ' ' . $endTime)->addDay();

            return ($datetime->gte($startDateTime) && $datetime->lte($endOfDay)) ||
                   ($datetime->gte($startOfNextDay) && $datetime->lte($endOfNextDay));
        } else {
            // Trong cùng ngày
            return $datetime->gte($startDateTime) && $datetime->lte($endDateTime);
        }
    }
}
