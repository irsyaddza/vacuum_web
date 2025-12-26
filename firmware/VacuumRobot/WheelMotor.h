#ifndef WHEEL_MOTOR_H
#define WHEEL_MOTOR_H

#include <Arduino.h>

class WheelMotor {
public:
    void begin();
    void moveForward();     // Maju
    void moveBackward();    // Mundur
    void turnLeft();        // Belok kiri
    void turnRight();       // Belok kanan
    void stop();
    void setSpeed(int pwm); // 0-255

private:
    int _speed = 0;
};

#endif
