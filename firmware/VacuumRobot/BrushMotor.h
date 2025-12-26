#ifndef BRUSH_MOTOR_H
#define BRUSH_MOTOR_H

#include <Arduino.h>

class BrushMotor {
public:
    void begin();
    void forward();   // Motor sapu berputar maju
    void reverse();   // Motor sapu berputar mundur
    void stop();
    void setSpeed(int pwm);  // 0-255

private:
    int _currentSpeed = 0;
    bool _isRunning = false;
};

#endif
