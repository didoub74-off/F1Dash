<?php

require "functions.php";

if($argc < 2) {
    echo 'Usage: php getResultsRace.php <url> <sessionId>' . PHP_EOL;
    exit;
}

$url = $argv[1];
$sessionId = $argv[2];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
curl_close($ch);

$response = preg_replace('/^\x{FEFF}/u',  '', $response); // Remove BOM

if(str_contains($response, '<?xml')) {
    $xml = simplexml_load_string($response);
    if ($xml->getName() == 'Error') {
        echo 'Error: ' . $xml->Message . PHP_EOL;
        exit;
    }
}
$driverInfo = json_decode($response, true);

usort($driverInfo, function($a, $b) {
    return $a['Position'] <=> $b['Position'];
});

$results = array();
$db = new SQLite3('../api_database');

foreach ($driverInfo as $driver) {
    $driverId = get_driver_from_code($driver['RacingNumber']);

    $stmt = $db->prepare('SELECT * FROM laps WHERE session_id = :session AND driver_id = :driver');
    $stmt->bindValue(':session', $sessionId);
    $stmt->bindValue(':driver', $driverId);

    $result = $stmt->execute();

    $timeForDriver = 0;

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $timeForDriver += $row['time'];
    }

    // Convert the time to a string
    // Round the time to 3 decimal places
    $timeForDriver = round($timeForDriver, 3);
    $seconds = floor($timeForDriver);
    $milliseconds = ($timeForDriver - $seconds) * 1000;

    $timeForDriverFormatted = gmdate('H:i:s', $seconds) . '.' . str_pad((int)$milliseconds, 3, '0', STR_PAD_LEFT);

// Check if the result already exists
    $stmt = $db->prepare('SELECT * FROM results WHERE session_id = :session AND driver_id = :driver');
    $stmt->bindValue(':session', $sessionId);
    $stmt->bindValue(':driver', $driverId);

    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if ($row) {
        continue;
    }
    // Remove _race from the session id (bahrain_2024_race)
    $id = $sessionId . '_' . $driverId;

    $stmt = $db->prepare('INSERT INTO results (id, session_id, driver_id, position, time) VALUES (:id, :session, :driver, :position, :time)');
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':session', $sessionId);
    $stmt->bindValue(':driver', $driverId);
    $stmt->bindValue(':position', $driver['Position']);
    $stmt->bindValue(':time', $timeForDriver);

    $stmt->execute();

    print 'Results for Driver #' . $driverId . ' added' . PHP_EOL;
}