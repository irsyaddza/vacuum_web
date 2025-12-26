#include "BrushMotor.h"
#include "config.h"

// PWM Configuration for ESP32 LEDC
const int BRUSH_PWM_FREQ = 5000;      // 5kHz
const int BRUSH_PWM_RESOLUTION = 8;   // 8-bit (0-255)

void BrushMotor::begin() {
    pinMode(PIN_BRUSH_FWD, OUTPUT);
    pinMode(PIN_BRUSH_REV, OUTPUT);
    
    // Setup LEDC PWM for ESP32
    ledcAttach(PIN_BRUSH_FWD, BRUSH_PWM_FREQ, BRUSH_PWM_RESOLUTION);
    ledcAttach(PIN_BRUSH_REV, BRUSH_PWM_FREQ, BRUSH_PWM_RESOLUTION);
    
    Serial.println("[BRUSH] Motor Sapu initialized:");
    Serial.print("  - Forward Pin: IO");
    Serial.println(PIN_BRUSH_FWD);
    Serial.print("  - Reverse Pin: IO");
    Serial.println(PIN_BRUSH_REV);
    
    stop();
}

void BrushMotor::setSpeed(int pwm) {
    _currentSpeed = constrain(pwm, 0, 255);
}

void BrushMotor::forward() {
    _isRunning = true;
    int speed = (_currentSpeed > 0) ? _currentSpeed : BRUSH_SPEED;
    
    ledcWrite(PIN_BRUSH_FWD, speed);  // Forward with PWM
    ledcWrite(PIN_BRUSH_REV, 0);       // Reverse OFF
    
    Serial.print("[BRUSH] Motor Sapu Forward @ PWM: ");
    Serial.println(speed);
}

void BrushMotor::reverse() {
    _isRunning = true;
    int speed = (_currentSpeed > 0) ? _currentSpeed : BRUSH_SPEED;
    
    ledcWrite(PIN_BRUSH_FWD, 0);       // Forward OFF
    ledcWrite(PIN_BRUSH_REV, speed);   // Reverse with PWM
    
    Serial.print("[BRUSH] Motor Sapu Reverse @ PWM: ");
    Serial.println(speed);
}

void BrushMotor::stop() {
    _isRunning = false;
    ledcWrite(PIN_BRUSH_FWD, 0);
    ledcWrite(PIN_BRUSH_REV, 0);
    Serial.println("[BRUSH] Motor Sapu STOPPED");
}
