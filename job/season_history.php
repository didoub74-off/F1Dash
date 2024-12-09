<?php
require '../functions.php';

// Only on terminal
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line');
}

$seasons = curl_get('https://f1connectapi.vercel.app/api/seasons?limit=1000');

// Order the seasons by year
usort($seasons['championships'], function ($a, $b) {
    return $a['year'] <=> $b['year'];
});
$historicData = [];
foreach ($seasons['championships'] as $season) {
    $year = $season['year'];

    $driverChampionship = curl_get("https://f1connectapi.vercel.app/api/$year/drivers-championship?limit=1000");
    $teamChampionship = curl_get("https://f1connectapi.vercel.app/api/$year/constructors-championship?limit=1000");
    $seasonRaces = curl_get("https://f1connectapi.vercel.app/api/$year");

    if(isset($teamChampionship['message']) && $teamChampionship['message'] === "No constructors championship found for this year. Try with other one.") {
        $teamChampionship = [
            'constructors_championship' => [
                [
                    'team' => [
                        'teamName' => 'No team',
                        'points' => 0
                    ]
                ]
            ]
        ];
    }
    $numberOfRaces = count($seasonRaces['races']);
    $numberOfDrivers = count($driverChampionship['drivers_championship']);
    $numberOfTeams = count($teamChampionship['constructors_championship']);

    $historicData[$year] = [
        'driver' => $driverChampionship['drivers_championship'][0],
        'team' => $teamChampionship['constructors_championship'][0],
        'numbers' => [
            'races' => $numberOfRaces,
            'drivers' => $numberOfDrivers,
            'teams' => $numberOfTeams
        ]
    ];

    print("Season: {$season['year']}\n");
}

// Reset the file with the new data
file_put_contents('../data/seasons.json', json_encode($historicData, JSON_PRETTY_PRINT));