#ifndef BATTERY_MONITOR_H
#define BATTERY_MONITOR_H

#include <Arduino.h>

class BatteryMonitor {
public:
    void begin();
    float getVoltage();
    int getPercentage();
    
private:
    // Calibration factor for Voltage Divider
    // Vout = Vin * (R2 / (R1 + R2))
    // ADC reads Vout. Vin = ADC * (3.3 / 4095) * ((R1+R2)/R2)
    // Using R1=10kΩ, R2=4.7kΩ: (10k + 4.7k) / 4.7k = 3.128
    const float _calibrationFactor = 3.128;
};

#endif
