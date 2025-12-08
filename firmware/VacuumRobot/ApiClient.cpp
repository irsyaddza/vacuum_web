#include "ApiClient.h"
#include "config.h"
#include <ArduinoJson.h> // Make sure you install ArduinoJson library

#include <WiFiManager.h> // PLEASE INSTALL: "WiFiManager" by tzapu

void ApiClient::connectWiFi() {
    Serial.println("=============================");
    Serial.println("Starting WiFi Manager...");
    
    WiFiManager wm;
    
    // UI Customization
    const char* custom_css = 
    "<style>"
    "body { background-color: #1a1a1a; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; }"
    "h1 { color: #4e73df; font-weight: bold; text-transform: uppercase; }"
    "button { background-color: #4e73df !important; border-radius: 25px !important; color: white !important; }"
    "input { background-color: #2d2d2d; color: white; border: 1px solid #444; border-radius: 5px; padding: 8px; }"
    "div { background-color: #2d2d2d; border-radius: 10px; padding: 10px; margin-bottom: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }"
    "a { color: #17a2b8; text-decoration: none; }"
    ".wrap { max-width: 400px; margin: 0 auto; padding: 20px; }"
    "</style>";
    wm.setCustomHeadElement(custom_css);
    
    // reset settings - wipe credentials for testing
    // wm.resetSettings(); 

    // Custom parameter for API URL (Optional, advanced)
    // WiFiManagerParameter custom_api_url("server", "API Base URL", API_BASE_URL, 60);
    // wm.addParameter(&custom_api_url);

    // Buka AP (Access Point) jika tidak connect
    // Nama WiFi Robot: "AutonomousVacRobot"
    // Password: "password123"
    bool res = wm.autoConnect("AutonomousVacRobot", "password123"); 

    if(!res) {
        Serial.println("Failed to connect");
        // ESP.restart();
    } 
    else {
        Serial.println(">>> WIFI CONNECTED SUCCESS! <<<");
        Serial.print("IP Address: ");
        Serial.println(WiFi.localIP());
        
        // Serial.print("API URL: ");
        // Serial.println(custom_api_url.getValue());
        Serial.println("=============================");
    }
}

void ApiClient::update() {
    if (WiFi.status() != WL_CONNECTED) return;

    unsigned long now = millis();

    // 0. Check Serial for "reset_wifi" command
    if (Serial.available()) {
        String cmd = Serial.readStringUntil('\n');
        cmd.trim(); // Remove whitespace
        if (cmd == "reset_wifi") {
            Serial.println("!!! RESETTING WIFI SETTINGS !!!");
            WiFiManager wm;
            wm.resetSettings();
            Serial.println("Settings cleared. Restarting ESP32...");
            delay(1000);
            ESP.restart();
        }
    }

    // 0.5 Check Physical Button
    checkResetButton();

    // 1. Poll Status
    if (now - _lastPollTime > API_POLL_INTERVAL) {
        _lastPollTime = now;
        getStatus(); 
    }
}

String ApiClient::getStatus() {
    HTTPClient http;
    String url = String(API_BASE_URL) + "/status";
    
    http.begin(url);
    int httpCode = http.GET();
    
    if (httpCode == 200) {
        String payload = http.getString();
        _parseStatusResponse(payload);
        return payload;
    } else {
        Serial.println("Error getting status");
        return "";
    }
    http.end();
}

void ApiClient::sendBattery(int percent, float voltage) {
    if (WiFi.status() != WL_CONNECTED) return;
    
    HTTPClient http;
    String url = String(API_BASE_URL) + "/battery";
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    
    DynamicJsonDocument doc(200);
    doc["battery_percent"] = percent;
    doc["battery_voltage"] = voltage;
    doc["estimated_time"] = String(percent / 10.0) + "h"; // Simple mock estimate
    
    String json;
    serializeJson(doc, json);
    
    int httpCode = http.POST(json);
    http.end();
}

void ApiClient::_parseStatusResponse(String json) {
    DynamicJsonDocument doc(512);
    deserializeJson(doc, json);
    
    if (doc["success"]) {
        String newState = String((const char*)doc["data"]["state"]);
        String newMode = String((const char*)doc["data"]["power_mode"]);
        int newVal = doc["data"]["power_value"];
        
        // Log changes
        if (newState != lastState) {
            Serial.print(">>> COMMAND RECEIVED: State changed from ");
            Serial.print(lastState);
            Serial.print(" to ");
            Serial.println(newState);
        }

        if (newMode != lastPowerMode) {
             Serial.print(">>> COMMAND RECEIVED: Power Mode changed from ");
             Serial.print(lastPowerMode);
             Serial.print(" to ");
             Serial.println(newMode);
        }
        
        lastState = newState;
        lastPowerMode = newMode;
        lastPowerValue = newVal;
    }
}

void ApiClient::checkResetButton() {
    // Logic: Tekan tahan 5 detik untuk reset
    if (digitalRead(PIN_WIFI_RESET) == LOW) { // Button Pressed (Active Low)
        unsigned long startPress = millis();
        bool longPress = false;
        
        Serial.print("Reset Button Pressed. Hold for 5 seconds...");
        
        while (digitalRead(PIN_WIFI_RESET) == LOW) {
            if (millis() - startPress > 5000) {
                longPress = true;
                Serial.println(" REQUIRED TIME REACHED!");
                break;
            }
            delay(100);
            Serial.print(".");
        }
        
        if (longPress) {
            Serial.println("\n!!! RESETTING WIFI SETTINGS !!!");
            WiFiManager wm;
            wm.resetSettings();
            Serial.println("Settings cleared. Restarting...");
            delay(1000);
            ESP.restart();
        } else {
             Serial.println("\nButton released too early. Cancelled.");
        }
    }
}