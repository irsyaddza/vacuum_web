<?php

namespace App\Http\Controllers;

use App\Models\Dayahisap;
use App\Models\VacuumStatus;
use App\Models\BatteryLog;
use Illuminate\Http\Request;

class VacuumAPIController extends Controller
{
    /**
     * GET /api/vacuum/status
     * Mengambil status vacuum terbaru (untuk ESP32)
     * Digunakan ESP32 untuk polling command dari web
     */
    public function getStatus()
    {
        try {
            $vacuumStatus = VacuumStatus::first();
            
            if (!$vacuumStatus) {
                // Buat default jika belum ada
                $vacuumStatus = VacuumStatus::create([
                    'state' => 'standby',
                    'power_mode' => 'normal',
                    'power_value' => 200
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'state' => $vacuumStatus->state,           // standby, working, stopping, returning, charging
                    'power_mode' => $vacuumStatus->power_mode, // eco, normal, strong
                    'power_value' => $vacuumStatus->power_value, // 150, 200, 255
                    'updated_at' => $vacuumStatus->updated_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/vacuum/command
     * Update state/command vacuum (dari web app)
     * Commands: start, stop, return_home
     */
    public function sendCommand(Request $request)
    {
        try {
            $validated = $request->validate([
                'command' => 'required|in:start,stop,return_home'
            ]);

            $stateMap = [
                'start' => 'working',
                'stop' => 'stopping',
                'return_home' => 'returning'
            ];

            $vacuumStatus = VacuumStatus::first() ?? new VacuumStatus();
            $vacuumStatus->state = $stateMap[$validated['command']];
            $vacuumStatus->save();

            return response()->json([
                'success' => true,
                'message' => 'Command sent: ' . $validated['command'],
                'data' => [
                    'state' => $vacuumStatus->state,
                    'updated_at' => $vacuumStatus->updated_at
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * POST /api/vacuum/power-mode
     * Update daya hisap (dari web app)
     * Modes: eco (150), normal (200), strong (255)
     */
    public function setPowerMode(Request $request)
    {
        try {
            $validated = $request->validate([
                'mode' => 'required|in:eco,normal,strong',
                'value' => 'required|integer|between:150,255'
            ]);

            $vacuumStatus = VacuumStatus::first() ?? new VacuumStatus();
            $vacuumStatus->power_mode = $validated['mode'];
            $vacuumStatus->power_value = $validated['value'];
            $vacuumStatus->save();

            // Simpan ke history daya hisap
            Dayahisap::create([
                'value' => $validated['value'],
                'mode' => $validated['mode']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Power mode updated',
                'data' => [
                    'mode' => $vacuumStatus->power_mode,
                    'value' => $vacuumStatus->power_value,
                    'updated_at' => $vacuumStatus->updated_at
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * POST /api/vacuum/battery
     * ESP32 mengirim data battery ke server
     */
    public function updateBattery(Request $request)
    {
        try {
            $validated = $request->validate([
                'battery_percent' => 'required|integer|between:0,100',
                'battery_voltage' => 'nullable|numeric',
                'estimated_time' => 'nullable|string'
            ]);

            // Simpan ke battery log
            $batteryLog = BatteryLog::create([
                'battery_percent' => $validated['battery_percent'],
                'battery_voltage' => $validated['battery_voltage'] ?? null,
                'estimated_time' => $validated['estimated_time'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Battery data received',
                'data' => [
                    'battery_percent' => $batteryLog->battery_percent,
                    'battery_voltage' => $batteryLog->battery_voltage,
                    'received_at' => $batteryLog->created_at
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * GET /api/vacuum/battery/latest
     * Mengambil battery data terbaru (untuk menampilkan di web)
     */
    public function getLatestBattery()
    {
        try {
            $batteryLog = BatteryLog::latest()->first();

            if (!$batteryLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'battery_percent' => $batteryLog->battery_percent,
                    'battery_voltage' => $batteryLog->battery_voltage,
                    'estimated_time' => $batteryLog->estimated_time,
                    'updated_at' => $batteryLog->created_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/vacuum/battery/history
     * Mengambil history battery (untuk graph/chart)
     */
    public function getBatteryHistory($minutes = 60)
    {
        try {
            $batteryLogs = BatteryLog::where('created_at', '>=', now()->subMinutes($minutes))
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $batteryLogs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/vacuum/full-status
     * Mengambil semua status vacuum sekaligus (state + power + battery)
     * Useful untuk dashboard
     */
    public function getFullStatus()
    {
        try {
            $vacuumStatus = VacuumStatus::first();
            $batteryLog = BatteryLog::latest()->first();

            return response()->json([
                'success' => true,
                'vacuum' => [
                    'state' => $vacuumStatus->state ?? 'standby',
                    'power_mode' => $vacuumStatus->power_mode ?? 'normal',
                    'power_value' => $vacuumStatus->power_value ?? 200,
                    'updated_at' => $vacuumStatus->updated_at ?? now()
                ],
                'battery' => [
                    'percent' => $batteryLog->battery_percent ?? 0,
                    'voltage' => $batteryLog->battery_voltage ?? null,
                    'estimated_time' => $batteryLog->estimated_time ?? null,
                    'updated_at' => $batteryLog->created_at ?? now()
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}