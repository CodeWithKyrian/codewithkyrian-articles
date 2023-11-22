<?php

declare(strict_types=1);
class Vehicle {
    public function startEngine() {
        echo "Starting the engine of the vehicle.\n";
    }
    public function move() {
        echo "Driving a vehicle\n";
    }
}

class Car extends Vehicle {
    public function startEngine(): void
    {
        echo "Starting the engine of the car with a key.\n";
    }
    public function move() {
        echo "Driving a car with 4 wheels\n";
    }
}

class Motorcycle extends Vehicle {
    public function startEngine() {
        echo "Starting the engine of the motorcycle with a kick.\n";
    }
    public function move() {
        echo "Driving a motorcycle with 2 wheels\n";
    }
}

class Bicycle extends Vehicle {
    public function startEngine() {
        echo "Bicycles don't have engines. Just pedal.\n";
    }
    public function move() {
        echo "Driving a bicycle with 2 wheels\n";
    }
}

class Player {
    private Vehicle $vehicle;

    public function setVehicle(Vehicle $vehicle) {
        $this->vehicle = $vehicle;
    }

    public function drive() {
        $this->vehicle->startEngine();
        $this->vehicle->move();
    }
}

$car = new Car();
$motorcycle = new Motorcycle();
$bicycle = new Bicycle();

$player = new Player();

$player->setVehicle($car);
$player->drive(); // Starting the engine of the car with a key. Driving a car with 4 wheels

$player->setVehicle($motorcycle);
$player->drive();
