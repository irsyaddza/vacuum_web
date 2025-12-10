#include "RobotController.h"
#include "config.h"
#include "MotorDriver.h"
#include "VacuumMotor.h"
#include "SensorArray.h"
#include "BatteryMonitor.h"
#include "ApiClient.h"

// External objects
extern MotorDriver motor;
extern VacuumMotor vacuum;
extern SensorArray sensors;
extern BatteryMonitor battery;
extern ApiClient api;

void RobotController::begin() {
    Serial.println("[ROBOT] Controller initialized - Basic control mode");
    stopAll();
}

void RobotController::update() {
    // 1. Battery Reporting
    if (millis() - _lastBatteryCheck > BATTERY_SEND_INTERVAL) {
        _lastBatteryCheck = millis();
        int pct = battery.getPercentage();
        float volt = battery.getVoltage();
        api.sendBattery(pct, volt);
    }

    // 2. Simple State Machine - FOCUS ONLY ON VACUUM CONTROL
    String targetState = api.lastState;
    int targetPower = api.lastPowerValue;
    
    // Log state changes
    if (targetState != _prevState) {
        Serial.print("[ROBOT] State changed: '");
        Serial.print(_prevState);
        Serial.print("' -> '");
        Serial.print(targetState);
        Serial.println("'");
        _prevState = targetState;
    }
    
    // Log power changes
    if (targetPower != _prevPowerValue) {
        Serial.print("[ROBOT] Power changed: ");
        Serial.print(_prevPowerValue);
        Serial.print(" -> ");
        Serial.println(targetPower);
        _prevPowerValue = targetPower;
    }

    // SIMPLE LOGIC: Only run vacuum if state is "working"
    if (targetState == "working") {
        Serial.println("==================");
        Serial.println("[ROBOT] STATE = WORKING");
        Serial.print("[ROBOT] API lastPowerValue = ");
        Serial.println(targetPower);
        Serial. print("[ROBOT] Setting vacuum PWM to: ");
        Serial.println(targetPower);
        Serial.println("==================");
        
        // Set vacuum power
        vacuum.setPower(targetPower);
        
        // Move forward (basic drive)
        motor.moveForward();
        
    } else {
        // ANY other state -> STOP everything
        Serial.print("[ROBOT] STATE=");
        Serial.print(targetState);
        Serial.println(" -> ALL MOTORS STOP");
        
        stopAll();
    }
}

void RobotController::stopAll() {
    motor.stop();
    vacuum.stop();
    Serial.println("[ROBOT] >>> stopAll() executed <<<");
}

void RobotController::handleCleaning() {
    // Not used in simplified version
}

void RobotController::handleReturning() {
    // Not used in simplified version
}

void RobotController::checkSafety() {
    // Not used in simplified version
}
