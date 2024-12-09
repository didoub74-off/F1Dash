<?php

require '../functions.php';

//$seasons = array_keys(json_decode(file_get_contents('../data/seasons.json'), true));
$seasons = [
    '2021',
    '2022',
    '2023',
    '2024',
];


$meetings = [];
foreach ($seasons as $season) {
    $seasonMeetings = curl_get("https://f1connectapi.vercel.app/api/$season?limit=1000")['races'];

    $seasonMeeting = [];
    foreach ($seasonMeetings as $meeting) {
        $meetingId = $meeting['raceId'];
        $meetingName = $meeting['raceName'];
        $meetingCircuit = $meeting['circuit']['circuitId'];
        $round = $meeting['round'];

        $raceResults = curl_get("https://f1connectapi.vercel.app/api/$season/$round/race");
        if(!isset($raceResults['message'])) {
            $race = [
                'date' => $raceResults['races']['date'],
                'time' => $raceResults['races']['time'],
                'results' => $raceResults['races']['results']
            ];
        }else {
            $race = [];
        }

        print ("Fetched race results for $season $round\n");

        $qualyResults = curl_get("https://f1connectapi.vercel.app/api/$season/$round/qualy");
        if(!isset($qualyResults['message'])) {
            $qualy = [
                'date' => $qualyResults['races']['date'],
                'time' => $qualyResults['races']['time'],
                'results' => $qualyResults['races']['qualyResults']
            ];
        }else {
            $qualy = [];
        }

        print ("Fetched qualy results for $season $round\n");

        $fp1Results = curl_get("https://f1connectapi.vercel.app/api/$season/$round/fp1");
        if(!isset($fp1Results['message'])) {
            $fp1 = [
                'date' => $fp1Results['races']['date'],
                'time' => $fp1Results['races']['time'],
                'results' => $fp1Results['races']['FP1_Results']
            ];
        }else {
            $fp1 = [];
        }

        print ("Fetched fp1 results for $season $round\n");

        $fp2Results = curl_get("https://f1connectapi.vercel.app/api/$season/$round/fp2");
        if(!isset($fp2Results['message'])) {
            $fp2 = [
                'date' => $fp2Results['races']['date'],
                'time' => $fp2Results['races']['time'],
                'results' => $fp2Results['races']['FP2_Results']
            ];
        }
        else {
            $fp2 = [];
        }

        print ("Fetched fp2 results for $season $round\n");

        $fp3Results = curl_get("https://f1connectapi.vercel.app/api/$season/$round/fp3");
        if(!isset($fp3Results['message'])) {
            $fp3 = [
                'date' => $fp3Results['races']['date'],
                'time' => $fp3Results['races']['time'],
                'results' => $fp3Results['races']['FP3_Results']
            ];
        }
        else {
            $fp3 = [];
        }

        print ("Fetched fp3 results for $season $round\n");

        $sprintQualyResults = curl_get("https://f1connectapi.vercel.app/api/$season/$round/sprint/qualy");
        if(!isset($sprintQualyResults['message'])) {
            $sprintQualy = [
                'date' => $sprintQualyResults['races']['date'],
                'time' => $sprintQualyResults['races']['time'],
                'results' => $sprintQualyResults['races']['sprintQualyResults']
            ];
        } else {
            $sprintQualy = [];
        }

        print ("Fetched sprint qualy results for $season $round\n");

        $sprintResults = curl_get("https://f1connectapi.vercel.app/api/$season/$round/sprint/race");
        if(!isset($sprintResults['message'])) {
            $sprint = [
                'date' => $sprintResults['races']['date'] ?? null,
                'time' => $sprintResults['races']['time'] ?? null,
                'results' => $sprintResults['races']['sprintRaceResults']
            ];
        }else {
            $sprint = [];
        }

        print ("Fetched sprint results for $season $round\n");

        $seasonMeeting[] = [
            'meetingId' => $meetingId,
            'round' => $round,
            'meetingName' => $meetingName,
            'meetingCircuit' => $meetingCircuit,
            'results' => [
                'fp1' => $fp1,
                'fp2' => $fp2,
                'fp3' => $fp3,
                'qualy' => $qualy,
                'sprintQualy' => $sprintQualy,
                'sprint' => $sprint,
                'race' => $race
            ]
        ];

        print ("Fetched all results for $season $round\n");
    }
    $meetings[$season] = $seasonMeeting;

    print "Season $season done\n";
}

$meetings = json_encode($meetings);
file_put_contents('../data/meetings.json', $meetings);