<?php
ini_set('memory_limit', '512M');

//$url = readline("Enter the URL of the API: ");

// Use argument instead of readline
if ($argc < 2) {
    echo 'Usage: php getLapsTime.php <url>' . PHP_EOL;
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
$response = preg_replace('/^\x{FEFF}/u', '', $response); // Remove BOM

// Check if the response is a XML file with an error message
if (str_contains($response, '<?xml')) {
    $xml = simplexml_load_string($response);
    if ($xml->getName() == 'Error') {
        echo 'Error: ' . $xml->Message . PHP_EOL;
        exit;
    }
}

// Check if the URL contains "Qualifying"
if (str_contains($url, 'Qualifying')) {
    echo 'This is a Qualifying session, please use the getQualifyingTime.php script' . PHP_EOL;
    exit;
}

// Convert the response to an associative array 00:00:02.353{"Lines":{},"Withheld":false} -> {"00:00:02.353":{"Lines":{},"Withheld":false}}
$lines = explode("\n", $response);
unset($response);
unset ($ch);
$timingData = array();

print 'Processing data...' . PHP_EOL;
$seenTimestamps = [];
foreach ($lines as &$entry) {
    // Extract the timestamp (first part of the string)
    preg_match('/^(\d{2}:\d{2}:\d{2}\.\d{3})/', $entry, $matches);
    $timestamp = $matches[1] ?? null;

    if ($timestamp) {
        // Adjust the timestamp if necessary
        while (isset($seenTimestamps[$timestamp])) {
            $timeParts = explode('.', $timestamp);
            $milliseconds = (int)$timeParts[1] + 1;
            $timestamp = $timeParts[0] . '.' . str_pad($milliseconds, 3, '0', STR_PAD_LEFT);
        }
        // Mark this timestamp as seen
        $seenTimestamps[$timestamp] = true;

        // Replace the original timestamp in the entry
        $entry = preg_replace('/^\d{2}:\d{2}:\d{2}\.\d{3}/', $timestamp, $entry);
    }
}


print 'Processing data... Step 2' . PHP_EOL;

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

print 'Done Processing data...' . PHP_EOL;

$laps = array();
foreach ($timingData as $data) {
    if (!empty($data['Lines'])) {
        $data = $data['Lines'];
        foreach ($data as $code => $driver) {
            if (!isset($laps[$code])) {
                $laps[$code] = array();
            }
            if (isset($driver['NumberOfLaps'])) {
                $laps[$code][$driver['NumberOfLaps']] = $driver['LastLapTime']['Value'] ?? null;
            }
        }
    }
}


$databasename = '../api_database';
$pdo = new PDO('sqlite:' . $databasename);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if the session id is already in the database
$stmt = $pdo->prepare('SELECT * FROM sessions WHERE id = :id');
$stmt->execute(array(':id' => $sessionId));
$session = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$session) {
    echo 'Session not found in the database' . PHP_EOL;
    echo 'Please enter the good session id or close the script to create the session' . PHP_EOL;
    exit;
}

// Save  the laps into laps.json

foreach ($laps as $code => $lap) {
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
            exit;
        }
    }

    if (!$driver) {
        echo 'Driver (#' . $code . ') not found in the database' . PHP_EOL;
        echo 'Please enter the good driver id or close the script to create the driver' . PHP_EOL;
        exit;
    }
    if ($driver) {
        $driverId = $driver['id'];
        foreach ($lap as $lapNumber => $lapTime) {
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
            $lap = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$lap) {
                $stmt = $pdo->prepare('INSERT INTO laps (id, session_id, driver_id, time) VALUES (:id, :session_id, :driver_id, :time)');
                $stmt->execute(array(':id' => $lapTimeId, ':session_id' => $sessionId, 'driver_id' => $driverId, ':time' => $lapTime));
            }
        }
        print 'Laps for Driver #' . $code . ' added' . PHP_EOL;
    }
}