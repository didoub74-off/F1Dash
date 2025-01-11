<?php

if(isset($_GET['meeting'])) {
    $meeting = $_GET['meeting'];
} else {
    echo json_encode(array('error' => 'No meeting specified'), JSON_PRETTY_PRINT);
    exit;
}
$db = new SQLite3('../database/api_database');

// Get all the results from the championship table
$results = $db->prepare('SELECT * FROM sessions WHERE meeting_id = :meeting');
$results->bindValue(':meeting', $meeting);
$stmt = $results->execute();

$json = array();
while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {
    $json[] = $row;
}

if (empty($json)) {
    echo json_encode(array('error' => 'No sessions found for this meeting'), JSON_PRETTY_PRINT);
    exit;
}

echo json_encode($json, JSON_PRETTY_PRINT);
