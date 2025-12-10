#ifndef CONFIG_H
#define CONFIG_H

// ===== PIN DEFINITIONS =====

// Drive Motors - Direction Control Only (L298N IN1/IN2 → OUT1&2)
// Motor roda terhubung ke OUT1 & OUT2
#define PIN_MOTOR_DIR_1     23  // IO23 → IN1 (Forward)
#define PIN_MOTOR_DIR_2     25  // IO25 → IN2 (Backward)

// Vacuum Motors - PWM Control (L298N IN3/IN4 → OUT3&4)
// Motor vakum terhubung ke OUT3 & OUT4
// PWM value diambil dari website (eco: 150, normal: 200, strong: 255)
#define PIN_VACUUM_PWM_1    26  // IO26 → IN3 (Vacuum Motor)
#define PIN_VACUUM_PWM_2    32  // IO32 → IN4 (Vacuum Motor)

// Sensors (IR Obstacle Avoidance) - Digital Input
#define PIN_IR_LEFT         16  // IO16 → IR1 Kiri
#define PIN_IR_FRONT        17  // IO17 → IR2 Tengah
#define PIN_IR_RIGHT        18  // IO18 → IR3 Kanan

// Sensors (IR Cliff Detection) - Digital Input
#define PIN_CLIFF_LEFT      19  // IO19 → IR4 Kiri
#define PIN_CLIFF_FRONT     21  // IO21 → IR5 Tengah
#define PIN_CLIFF_RIGHT     22  // IO22 → IR6 Kanan

// Battery (Voltage Divider) - Analog Input
#define PIN_BATTERY_ADC     36  // VP pin

// Function Buttons
#define PIN_WIFI_RESET      0   // Tombol BOOT bawaan ESP32. Tekan tahan 5 detik.

// ===== API CONFIG =====
#define API_BASE_URL        "http://10.121.65.67:8000/v1/vacuum"
#define API_POLL_INTERVAL   2000    // ms - polling status dari server
#define BATTERY_SEND_INTERVAL 60000 // ms - kirim data battery ke server

// ===== MOTOR SETTINGS =====
#define VACUUM_POWER_NORMAL 200  // Default PWM untuk vacuum NORMAL mode (0-255)

#endif
