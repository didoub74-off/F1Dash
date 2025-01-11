<?php

if(isset($_GET['session'])) {
    $session = $_GET['session'];
} else {
    echo json_encode(array('error' => 'No session specified'), JSON_PRETTY_PRINT);
    exit;
}

$db = new SQLite3('../database/api_database');

$results = $db->prepare('SELECT * FROM sessions WHERE id = :session');
$results->bindValue(':session', $session);
$sessionInfo = $results->execute();

$results = $db->prepare('SELECT * FROM results WHERE session_id = :session');
$results->bindValue(':session', $session);
$sessionResults = $results->execute();

$sessionResultsArray = array();
while ($row = $sessionResults->fetchArray(SQLITE3_ASSOC)) {
    $driverId = $row['driver_id'];
    $driver = $db->prepare('SELECT * FROM drivers WHERE id = :driver');
    $driver->bindValue(':driver', $driverId);
    $driver = $driver->execute();

    $driver = $driver->fetchArray(SQLITE3_ASSOC);
    $row['driver'] = $driver;

    $time = $row['time'];
    // Encode the time to string with hours, minutes, seconds and milliseconds


    unset($row['session_id']);
    unset($row['driver_id']);
    unset($row['id']);
    $sessionResultsArray[] = $row;
}

// Get the total time for the 1st driver and add the interval to the other drivers

if(count($sessionResultsArray) == 0) {
    echo json_encode(array('error' => 'No results have been fetched for this session yet.'), JSON_PRETTY_PRINT);
    exit;
}
$firstDriver = $sessionResultsArray[0];
$firstDriverTime = $firstDriver['time'];

$results = $db->prepare('SELECT * FROM laps WHERE session_id = :session');
$results->bindValue(':session', $session);
$sessionLaps = $results->execute();
$lapCountBest = 0;
foreach ($sessionResultsArray as $key => $driver) {
    $time = $driver['time'];
    $interval = $time - $firstDriverTime;
    $interval = round($interval, 3);

    $lapCount = $db->prepare('SELECT COUNT(*) FROM laps WHERE session_id = :session AND driver_id = :driver');
    $lapCount->bindValue(':session', $session);
    $lapCount->bindValue(':driver', $driver['driver']['id']);
    $lapCount = $lapCount->execute();
    $lapCount = $lapCount->fetchArray(SQLITE3_ASSOC);
    $sessionResultsArray[$key]['laps'] = $lapCount['COUNT(*)'];

    if($lapCount['COUNT(*)'] > $lapCountBest) {
        $lapCountBest = $lapCount['COUNT(*)'];
    }

    if($lapCount['COUNT(*)'] < $lapCountBest) {
        $sessionResultsArray[$key]['interval'] = 'LAP (+' . ($lapCountBest - $lapCount['COUNT(*)']) . ')';
    }else {
        $sessionResultsArray[$key]['interval'] = $interval;
    }
}

$laps = array();
while ($row = $sessionLaps->fetchArray(SQLITE3_ASSOC)) {
    $driverId = $row['driver_id'];
    if(!isset($laps[$driverId])) {
        $laps[$driverId] = array();
    }
    if($row['time'] != null) {
        // Put 3 decimal places on the time - Even if it's 0
        $row['time'] = number_format($row['time'], 3);
    }

    unset($row['session_id']);
    unset($row['driver_id']);
    $driver = $db->prepare('SELECT * FROM drivers WHERE id = :driver');
    $driver->bindValue(':driver', $driverId);
    $driver = $driver->execute();

    $driver = $driver->fetchArray(SQLITE3_ASSOC);
    $row['driver'] = $driver;

    $laps[$driverId][] = $row;
}

$json = [
    'sessionInfo' => $sessionInfo->fetchArray(SQLITE3_ASSOC),
    'sessionResults' => $sessionResultsArray,
    'sessionsLaps' => $laps
];

echo json_encode($json, JSON_PRETTY_PRINT);