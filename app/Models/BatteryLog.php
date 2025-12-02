<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatteryLog extends Model
{
    use HasFactory;

    protected $table = 'battery_logs';

    protected $fillable = [
        'battery_percent',
        'battery_voltage',
        'estimated_time'
    ];

    protected $casts = [
        'battery_percent' => 'integer',
        'battery_voltage' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the latest battery log
     */
    public static function getLatest()
    {
        return self::latest()->first();
    }

    /**
     * Get battery logs from last N minutes
     */
    public static function getHistoryByMinutes($minutes = 60)
    {
        return self::where('created_at', '>=', now()->subMinutes($minutes))
            ->orderBy('created_at', 'asc')
            ->get();
    }
}