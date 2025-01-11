<?php

$db = new SQLite3('../database/api_database');

$results = $db->query('SELECT * FROM drivers');
$drivers = array();
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $drivers[] = $row;
}

echo json_encode($drivers, JSON_PRETTY_PRINT);