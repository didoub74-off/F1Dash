<?php

if(isset($_GET['season'])) {
    $season = 'f1_' . $_GET['season'];
} else {
    $season = 'f1_' . 2024;
}
$db = new SQLite3('../database/api_database');

// Get all the results from the championship table
$results = $db->prepare('SELECT * FROM meetings WHERE championship_id = :season');
$results->bindValue(':season', $season);
$stmt = $results->execute();


$json = array();
while ($row = $stmt->fetchArray(SQLITE3_ASSOC)) {

    $circuit = $db->prepare('SELECT * FROM circuits WHERE id = :circuit');
    $circuit->bindValue(':circuit', $row['circuit_id']);
    $circuit = $circuit->execute();

    unset($row['circuit_id']);
    $circuit = $circuit->fetchArray(SQLITE3_ASSOC);
    $row['circuit'] = $circuit;

    $championship = $db->prepare('SELECT * FROM championships WHERE id = :championship');
    $championship->bindValue(':championship', $row['championship_id']);
    $championship = $championship->execute();

    $championship = $championship->fetchArray(SQLITE3_ASSOC);
    $row['championship'] = $championship;
    unset($row['championship_id']);
    $json[] = $row;
}

echo json_encode($json, JSON_PRETTY_PRINT);
