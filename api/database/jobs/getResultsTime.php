<?php

require "functions.php";

$results = array();

if ($argc < 1) {
    echo 'Usage: php getResultsTime.php <sessionId>' . PHP_EOL;
    exit;
}

$sessionId = $argv[1];

$db = new SQLite3('../api_database');

$stmt = $db->prepare('SELECT * FROM laps WHERE session_id = :session');
$stmt->bindValue(':session', $sessionId);

$result = $stmt->execute();


$sessionResults = array();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $driverId = $row['driver_id'];
    $time = $row['time'];

    if (!isset($sessionResults[$driverId])) {
        $sessionResults[$driverId] = 1000;
    }

    if($sessionResults[$driverId] >= $time && $time > 0 && $time != null) {
        $sessionResults[$driverId] = $time;
    }else {
        if($sessionResults[$driverId] == 1000) {
            $sessionResults[$driverId] = "DNF";
        }
    }
}

// Order the results by smaller time and add the position

asort($sessionResults);

$position = 1;

foreach ($sessionResults as $driverId => $time) {
    $stmt = $db->prepare('SELECT * FROM drivers WHERE id = :id');
    $stmt->bindValue(':id', $driverId);

    $result = $stmt->execute();
    $driver = $result->fetchArray(SQLITE3_ASSOC);

    $time = round($time, 3);
    $seconds = floor($time);
    $milliseconds = ($time - $seconds) * 1000;

    $timeFormatted = gmdate('H:i:s', $seconds) . '.' . str_pad((int)$milliseconds, 3, '0', STR_PAD_LEFT);


    // Check if the result already exists
    $stmt = $db->prepare('SELECT * FROM results WHERE session_id = :session AND driver_id = :driver');
    $stmt->bindValue(':session', $sessionId);
    $stmt->bindValue(':driver', $driverId);

    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row) {
        continue;
    }

    $stmt = $db->prepare('INSERT INTO results (id, session_id, driver_id, position, time) VALUES (:id, :session, :driver, :position, :time)');
    $stmt->bindValue(':id', $sessionId . "_" . $driverId);
    $stmt->bindValue(':session', $sessionId);
    $stmt->bindValue(':driver', $driverId);
    $stmt->bindValue(':position', $position);
    $stmt->bindValue(':time', $time);

    $stmt->execute();

    $position++;
}

