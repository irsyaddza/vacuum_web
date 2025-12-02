<?php

// ===== File 1: app/Models/VacuumStatus.php =====

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacuumStatus extends Model
{
    use HasFactory;

    protected $table = 'vacuum_statuses';

    protected $fillable = [
        'state',
        'power_mode',
        'power_value'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the latest vacuum status
     */
    public static function getLatest()
    {
        return self::first() ?? self::create([
            'state' => 'standby',
            'power_mode' => 'normal',
            'power_value' => 200
        ]);
    }
}