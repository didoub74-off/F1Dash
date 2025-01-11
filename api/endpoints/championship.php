<?php

$db = new SQLite3('../database/api_database');

// Get all the results from the championship table
$results = $db->query('SELECT * FROM championships');

$json = array();
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    $json[] = $row;
}

echo json_encode($json, JSON_PRETTY_PRINT);
