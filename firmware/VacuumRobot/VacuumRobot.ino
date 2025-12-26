#include <Arduino.h>
#include "config.h"
#include "BrushMotor.h"
#include "VacuumMotor.h"
#include "WheelMotor.h"
#include "SensorArray.h"
#include "BatteryMonitor.h"
#include "ApiClient.h"
#include "RobotController.h"

// Global Objects
BrushMotor brush;
VacuumMotor vacuum;
WheelMotor wheels;
SensorArray sensors;
BatteryMonitor battery;
ApiClient api;
RobotController robot;

void setup() {
  Serial.begin(115200);
  Serial.println("=== Vacuum Robot ESP32 Starting ===");

  // Initialize Hardware
  brush.begin();     // Motor Sapu (OUT1 & OUT2)
  vacuum.begin();    // Motor Vakum (OUT3 & OUT4)
  wheels.begin();    // Motor Roda (L298N #2)
  sensors.begin();
  battery.begin();

  // Initialize Network
  api.connectWiFi();

  // Initialize Logic
  robot.begin();
  
  Serial.println("\n=== ROBOT READY ===");
  Serial.println("Motor Configuration:");
  Serial.println("  - Motor Driver 1: Brush (OUT1/2) + Vacuum (OUT3/4)");
  Serial.println("  - Motor Driver 2: Wheel Motors (LEFT + RIGHT)");
  Serial.print("API Polling Interval: ");
  Serial.print(API_POLL_INTERVAL / 1000);
  Serial.println(" seconds");
  Serial.println("Monitor this output to see state/power changes.");
  Serial.println("===================\n");
}

void loop() {
  // Update all subsystems
  robot.update();
  api.update(); // Handle network polling
  
  delay(10); // Small delay for stability
}
