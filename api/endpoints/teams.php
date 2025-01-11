<?php
$db = new SQLite3('../database/api_database');

$results = $db->query('SELECT * FROM teams');
$teams = array();
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $teams[] = $row;
}

echo json_encode($teams, JSON_PRETTY_PRINT);