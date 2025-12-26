#include "RobotController.h"
#include "config.h"
#include "BrushMotor.h"
#include "VacuumMotor.h"
#include "WheelMotor.h"
#include "SensorArray.h"
#include "BatteryMonitor.h"
#include "ApiClient.h"

// External objects
extern BrushMotor brush;
extern VacuumMotor vacuum;
extern WheelMotor wheels;
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

    // 2. Simple State Machine
    String targetState = api.lastState;
    int targetPower = api.lastPowerValue;
    String direction = api.lastDirection;  // Arah: forward, backward, left, right, stop
    
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

    // LOGIC: Control based on state from website
    if (targetState == "working") {
        // Log only once when entering working state
        static bool wasWorking = false;
        if (!wasWorking) {
            Serial.println("==================");
            Serial.println("[ROBOT] STATE = WORKING - STARTING ALL MOTORS");
            Serial.print("[ROBOT] API lastPowerValue = ");
            Serial.println(targetPower);
            Serial.print("[ROBOT] Direction = '");
            Serial.print(direction);
            Serial.println("'");
            Serial.print("[ROBOT] Direction length = ");
            Serial.println(direction.length());
            Serial.println("==================");
            wasWorking = true;
        }
        
        // Set vacuum power
        vacuum.setPower(targetPower);
        
        // Start brush motor
        brush.forward();
        
        // Control wheel direction from website
        // DEBUG: Log wheel command
        static unsigned long lastWheelLog = 0;
        if (millis() - lastWheelLog > 2000) {  // Log every 2 seconds
            Serial.print("[WHEEL DEBUG] Calling moveForward, direction='");
            Serial.print(direction);
            Serial.println("'");
            lastWheelLog = millis();
        }
        
        // Default: moveForward jika direction kosong/stop/"" (robot jalan saat working)
        if (direction == "backward") {
            wheels.moveBackward();
        } else if (direction == "left") {
            wheels.turnLeft();
        } else if (direction == "right") {
            wheels.turnRight();
        } else if (direction == "stop") {
            wheels.stop();  // Explicit stop dari website
        } else {
            // Default: forward (jika direction kosong, "", atau "forward")
            wheels.moveForward();
        }
        
    } else {
        // ANY other state -> STOP everything
        static bool loggedStop = false;
        static String lastLoggedState = "";
        if (!loggedStop || lastLoggedState != targetState) {
            Serial.print("[ROBOT] STATE=");
            Serial.print(targetState);
            Serial.println(" -> ALL MOTORS STOP");
            loggedStop = true;
            lastLoggedState = targetState;
        }
        
        stopAll();
        
        // Reset working flag when not working
        // (Need extern bool)
    }
}

void RobotController::stopAll() {
    wheels.stop();
    brush.stop();
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
