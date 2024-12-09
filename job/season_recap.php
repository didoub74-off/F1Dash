<?php
require "../functions.php";
$data = [];
for($i=2024; $i >= 2020; $i--) {
    print("Processing season $i\n");
    $driverChampionship = curl_get("https://f1connectapi.vercel.app/api/$i/drivers-championship");
    $teamChampionship = curl_get("https://f1connectapi.vercel.app/api/$i/constructors-championship");
    $raceResults = curl_get("https://f1connectapi.vercel.app/api/$i");

    // Foreach $raceResult and get the biggest round number
    $numberOfRaces = 0;
    foreach ($raceResults['races'] as $race) {
        $round = $race['round'];
        if ($round > $numberOfRaces) {
            $numberOfRaces = $round;
        }
    }

    $stats = [
        'winner' => [
            'driver' => [],
            'team' => []
        ],
        'pole' => [
            'driver' => [],
            'team' => []
        ]
    ];
        for ($j=1; $j <= $numberOfRaces; $j++) {
        print (" - Processing round $j\n");
        $qualifying = curl_get("https://f1connectapi.vercel.app/api/$i/$j/qualy");
        if(!isset($qualifying['message']) || $qualifying['message'] !== "No qualy results found for this round. Try with other one.") {
            foreach ($qualifying['races']['qualyResults'] as $result) {
                if ($result['Grid_Position'] == 1) {
                    if (!array_key_exists($result['driver']['driverId'], $stats['pole']['driver'])) {
                        $stats['pole']['driver'][$result['driver']['driverId']] = 1;
                    } else {
                        $stats['pole']['driver'][$result['driver']['driverId']]++;
                    }

                    if (!array_key_exists($result['team']['teamId'], $stats['pole']['team'])) {
                        $stats['pole']['team'][$result['team']['teamId']] = 1;
                    } else {
                        $stats['pole']['team'][$result['team']['teamId']]++;
                    }
                }
            }
        }
        $race = curl_get("https://f1connectapi.vercel.app/api/$i/$j/race");
        if(!isset($race['message']) || $race['message'] !== "No race results found for this round. Try with other one.") {
            foreach ($race['races']['results'] as $result) {
                if ($result['position'] == 1) {
                    if (!array_key_exists($result['driver']['driverId'], $stats['winner']['driver'])) {
                        $stats['winner']['driver'][$result['driver']['driverId']] = 1;
                    } else {
                        $stats['winner']['driver'][$result['driver']['driverId']]++;
                    }

                    if (!array_key_exists($result['team']['teamId'], $stats['winner']['team'])) {
                        $stats['winner']['team'][$result['team']['teamId']] = 1;
                    } else {
                        $stats['winner']['team'][$result['team']['teamId']]++;
                    }
                }
            }
        }
    }

    $data[$i] = [
        "driverChampionship" => $driverChampionship,
        "teamChampionship" => $teamChampionship,
        "stats" => $stats
    ];
}
// Save the data to a file
file_put_contents("../data/recap.json", json_encode($data));