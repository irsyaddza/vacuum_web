#include "VacuumMotor.h"
#include "config.h"

// PWM Configuration for ESP32 LEDC
const int PWM_FREQ = 5000;      // 5kHz - same as user's working code
const int PWM_RESOLUTION = 8;   // 8-bit (0-255)

void VacuumMotor::begin() {
    pinMode(PIN_VACUUM_PWM_1, OUTPUT);
    pinMode(PIN_VACUUM_PWM_2, OUTPUT);
    
    // Setup LEDC PWM for ESP32 (NEW API)
    // This replaces ledcSetup + ledcAttachPin with single ledcAttach
    ledcAttach(PIN_VACUUM_PWM_1, PWM_FREQ, PWM_RESOLUTION);
    ledcAttach(PIN_VACUUM_PWM_2, PWM_FREQ, PWM_RESOLUTION);
    
    Serial.println("[VACUUM] Motor Vakum initialized:");
    Serial.print("  - Speed Pin: IO");
    Serial.println(PIN_VACUUM_PWM_1);
    Serial.print("  - Direction Pin: IO");
    Serial.println(PIN_VACUUM_PWM_2);
    
    stop();
}

void VacuumMotor::setPower(int pwm) {
    _currentPower = constrain(pwm, 0, 255);
    
    Serial.print("[");
    Serial.print(millis());
    Serial.println("] --- VACUUM MOTOR PWM WRITE ---");
    Serial.print("[VACUUM] Requested PWM: ");
    Serial.println(pwm);
    Serial.print("[VACUUM] Constrained PWM: ");
    Serial.println(_currentPower);
    
    // *** CRITICAL FIX for L298N Motor Driver ***
    // For L298N: ONE pin gets PWM (speed), OTHER pin gets 0 (direction)
    // PIN_VACUUM_PWM_1 (IO26/IN3) = PWM speed
    // PIN_VACUUM_PWM_2 (IO32/IN4) = 0 (forward direction)
    ledcWrite(PIN_VACUUM_PWM_1, _currentPower);  // Speed control
    ledcWrite(PIN_VACUUM_PWM_2, 0);              // Direction = forward
    
    Serial.print("[VACUUM] ✓ ledcWrite to PIN_VACUUM_PWM_1 (IO26): ");
    Serial.println(_currentPower);
    Serial.print("[VACUUM] ✓ ledcWrite to PIN_VACUUM_PWM_2 (IO32): ");
    Serial.println(0);
    Serial.println("------------------------------");
}

void VacuumMotor::start() {
    setPower(VACUUM_POWER_NORMAL);
}

void VacuumMotor::stop() {
    setPower(0);
}
