<?php

require "functions.php";

$results = array();
print 'This is a only for the Qualifying session' . PHP_EOL;

if ($argc < 1) {
    echo 'Usage: php getResultsQualifying.php <sessionId>' . PHP_EOL;
    exit;
}
$sessionIdEntered = $argv[1];

$db = new SQLite3('../api_database');

$laps = array(
    $sessionIdEntered . "_q1" => array(),
    $sessionIdEntered . "_q2" => array(),
    $sessionIdEntered . "_q3" => array()
);
for($i = 1; $i <= 3; $i++) {
    $sessionId = $sessionIdEntered . "_q" . $i;

    $stmt = $db->prepare('SELECT * FROM laps WHERE session_id = :session');
    $stmt->bindValue(':session', $sessionId);

    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $driverId = $row['driver_id'];
        $time = $row['time'];

        if (!isset($laps[$sessionId][$driverId])) {
            $laps[$sessionId][$driverId] = 1000;
        }

        if ($laps[$sessionId][$driverId] >= $time && $time > 0 && $time != null) {
            $laps[$sessionId][$driverId] = $time;
        } else {
            if ($laps[$sessionId][$driverId] == 1000) {
                $laps[$sessionId][$driverId] = "DNF";
            }
        }
    }
}
// Order the results by smaller time and add the position - for each session
$q = 0;
foreach ($laps as $sessionId => $sessionLaps) {
    $q++;
    asort($sessionLaps);

    // If it's Q1 only get the drivers after the 15th
    // If it's Q2 only get the drivers after the 10th
    // If it's Q3 get all the drivers

    $position = 1;

    foreach ($sessionLaps as $driverId => $time) {
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
        $stmt->bindValue(':session', $sessionIdEntered);
        $stmt->bindValue(':driver', $driverId);

        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        if ($row) {
            continue;
        }

        $stmt = $db->prepare('INSERT INTO results (id, session_id, driver_id, position, time) VALUES (:id, :session, :driver, :position, :time)');
        $stmt->bindValue(':id', $sessionIdEntered . "_qualifying_$q" . "_" . $driverId);
        $stmt->bindValue(':session', $sessionId);
        $stmt->bindValue(':driver', $driverId);
        $stmt->bindValue(':position', $position);
        $stmt->bindValue(':time', $time);

        $stmt->execute();

        $position++;
    }
}

