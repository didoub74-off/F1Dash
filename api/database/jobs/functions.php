<?php

function get_driver_from_code($code) : string {
    $databaseName = '../api_database';
    $pdo = new PDO('sqlite:' . $databaseName);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare('SELECT * FROM drivers WHERE number = :code');
    $stmt->execute(array(':code' => $code));
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if there is more than one driver with the same number
    if ($driver) {
        $stmt = $pdo->prepare('SELECT * FROM drivers WHERE number = :code');
        $stmt->execute(array(':code' => $code));
        $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($drivers) > 1) {
            echo 'There is more than one driver with the number ' . $code . PHP_EOL;
            echo 'Please enter the good driver id' . PHP_EOL;
            $id = readline();
            $stmt = $pdo->prepare('SELECT * FROM drivers WHERE id = :id');
            $stmt->execute(array(':id' => $id));
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$driver) {
        echo 'Driver (#' . $code . ') not found in the database' . PHP_EOL;
        echo 'Please enter the good driver id or close the script to create the driver' . PHP_EOL;
        $id = readline();
        $stmt = $pdo->prepare('SELECT * FROM drivers WHERE id = :id');
        $stmt->execute(array(':id' => $id));
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return $driver['id'];
}