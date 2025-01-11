<?php

require "functions.php";

//$url = readline("Enter the URL of the API: ");

if ($argc < 2) {
    echo 'Usage: php getQualifyingTime.php <url> <meeting_id>' . PHP_EOL;
    exit;
}
$url = $argv[1];
$sessionIdTyped = $argv[2];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
curl_close($ch);
$response = preg_replace('/^\x{FEFF}/u', '', $response); // Remove BOM

// Check if the response is an XML file with an error message
if (str_contains($response, '<?xml')) {
    $xml = simplexml_load_string($response);
    if ($xml->getName() == 'Error') {
        echo 'Error: ' . $xml->Message . PHP_EOL;
        exit;
    }
}

if (!str_contains($url, 'Qualifying')) {
    echo 'This is not a Qualifying session, please use the getLapsTime.php script' . PHP_EOL;
    exit;
}

// Convert the response to an associative array 00:00:02.353{"Lines":{},"Withheld":false} -> {"00:00:02.353":{"Lines":{},"Withheld":false}}
$lines = explode("\n", $response);
unset($response);
unset ($ch);
$timingData = array();

$seenTimestamps = [];

foreach ($lines as &$entry) {
    // Extract the timestamp (first part of the string)
    preg_match('/^(\d{2}:\d{2}:\d{2}\.\d{3})/', $entry, $matches);
    $timestamp = $matches[1] ?? null;

    if ($timestamp) {
        // If the timestamp is already seen, adjust it
        while (in_array($timestamp, $seenTimestamps)) {
            $timeParts = explode('.', $timestamp);
            $milliseconds = (int)$timeParts[1] + 1;
            $timestamp = $timeParts[0] . '.' . str_pad($milliseconds, 3, '0', STR_PAD_LEFT);
        }
        // Add the adjusted timestamp to the seen list
        $seenTimestamps[] = $timestamp;

        // Replace the original timestamp in the entry
        $entry = preg_replace('/^\d{2}:\d{2}:\d{2}\.\d{3}/', $timestamp, $entry);
    }
}

foreach ($lines as $line) {
    preg_match('/^(\d{2}:\d{2}:\d{2}\.\d{3})(.*)$/', $line, $matches);
    if ($matches) {
        $timestamp = $matches[1];
        $jsonData = json_decode($matches[2], true);

        // Merge with existing data for the same timestamp
        if (isset($timingData[$timestamp])) {
            $timingData[$timestamp] = array_merge_recursive($timingData[$timestamp], $jsonData);
        } else {
            $timingData[$timestamp] = $jsonData;
        }
    }
}

$laps = array();
$sessionPart = null;
foreach ($timingData as $data) {
    if (isset($data['SessionPart'])) {
        $sessionPart = $data['SessionPart'];
    }
    if (!empty($data['Lines'])) {
        $data = $data['Lines'];
        if (!isset($laps[$sessionPart])) {
            $laps[$sessionPart] = array();
        }
        foreach ($data as $code => $driver) {
            if (isset($driver['NumberOfLaps'])) {
                if (!isset($laps[$sessionPart][$code])) {
                    $laps[$sessionPart][$code] = array();
                }

                // Check if lap is more than 200 seconds
                if (isset($driver['LastLapTime']['Value']) && $driver['LastLapTime']['Value'] != null) {
                    $lapTime = explode(':', $driver['LastLapTime']['Value']);
                    $lapTime = (int)$lapTime[0] * 60 + (float)$lapTime[1];
                    if ($lapTime > 200) {
                        continue;
                    }
                }
                if ($driver['NumberOfLaps'] == "1") {
                    continue;
                }

                if (isset($driver['LastLapTime']['Value'])) {
                    $laps[$sessionPart][$code][] = $driver['LastLapTime']['Value'];
                }
            }
        }
    }
}

// Save to file $laps
file_put_contents('laps.json', json_encode($laps, JSON_PRETTY_PRINT));

$databaseName = '../api_database';
$pdo = new PDO('sqlite:' . $databaseName);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ask for the session id

foreach ($laps as $sessionPart => $lapsSession) {
    $sessionId = $sessionIdTyped . '_q' . $sessionPart;
    echo 'Processing session id ' . $sessionId . PHP_EOL;
    foreach ($lapsSession as $code => $lap) {
        $driverId = get_driver_from_code($code);
        print('Processing driver #' . $code . PHP_EOL);
        $count = count($lap);
        for ($lapNumber = 1; $lapNumber <= $count; $lapNumber++) {
            $index = $lapNumber - 1;

            $lapTime = $lap[$index];
            // Convert the lap time to seconds (1:48.349 -> 108.349)
            if ($lapTime != null) {
                $lapTime = explode(':', $lapTime);
                $lapTime = (int)$lapTime[0] * 60 + (float)$lapTime[1];
            } else {
                $lapTime = null;
            }

            $lapTimeId = "$sessionId-$code-$lapNumber";
            $stmt = $pdo->prepare('SELECT * FROM laps WHERE id = :id');
            $stmt->execute(array(':id' => $lapTimeId));
            $lapDb = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$lapDb) {
                $stmt = $pdo->prepare('INSERT INTO laps (id, session_id, driver_id, time) VALUES (:id, :session_id, :driver_id, :time)');
                $stmt->execute(array(':id' => $lapTimeId, ':session_id' => $sessionId, 'driver_id' => $driverId, ':time' => $lapTime));
            }
        }
        print 'Qualification laps for Driver #' . $code . ' added' . PHP_EOL;
    }
}