<?php
require '../functions.php';

$teams = [];

$meetings = json_decode(file_get_contents('../data/meetings.json'), true);
for($i=2021; $i<=2024; $i++) {
    $season = $i;
    $seasonMeetings = $meetings[$season];

    foreach($seasonMeetings as $meeting) {
        foreach($meeting['results'] as $session => $result) {
            if(empty($result)) {
                continue;
            }

            foreach ($result['results'] as $teamResult) {
                if(!isset($teams[$teamResult['team']['teamId']])) {
                    $teams[$teamResult['team']['teamId']] = [
                        'teamId' => $teamResult['team']['teamId'],
                        'teamName' => $teamResult['team']['teamName'],
                        'teamNationality' => $teamResult['team']['nationality'],
                        'firstAppearance' => $teamResult['team']['firstAppareance'],
                        'constructorChampionships' => $teamResult['team']['constructorsChampionships'],
                        'stats' => [
                            'appearances' => []
                        ]
                    ];
                }

                if(!isset($teams[$teamResult['team']['teamId']]['stats']['appearances'][$session])) {
                    $teams[$teamResult['team']['teamId']]['stats']['appearances'][$session] = 1;
                } else {
                    $teams[$teamResult['team']['teamId']]['stats']['appearances'][$session]++;
                }

                if($session == "race" && $teamResult['position'] == 1) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['race']['wins']['total'])) {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['wins']['total'] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['wins']['total']++;
                    }

                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['race']['wins'][$season])) {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['wins'][$season] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['wins'][$season]++;
                    }
                }

                if($session == "race" && ($teamResult['position'] <= 3)) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['race']['podiums']['total'])) {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['podiums']['total'] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['podiums']['total']++;
                    }

                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['race']['podiums'][$season])) {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['podiums'][$season] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['podiums'][$season]++;
                    }
                }

                if($session == "race"  && isset($teamResult['position'])) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['race']['positions'][$teamResult['position']])) {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['positions'][$teamResult['position']] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['positions'][$teamResult['position']]++;
                    }

                    ksort($teams[$teamResult['team']['teamId']]['stats']['race']['positions']);
                }

                if($session == "qualy" && $teamResult['Grid_Position'] == 1) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['race']['poles']['total'])) {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['poles']['total'] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['poles']['total']++;
                    }

                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['race']['poles'][$season])) {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['poles'][$season] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['race']['poles'][$season]++;
                    }
                }

                if($session == "sprintQualy" && $teamResult['Grid_Position'] == 1) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['sprint']['poles']['total'])) {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['poles']['total'] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['poles']['total']++;
                    }

                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['sprint']['poles'][$season])) {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['poles'][$season] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['poles'][$season]++;
                    }
                }

                if($session == "sprint" && $teamResult['position'] == 1) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['sprint']['wins']['total'])) {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['wins']['total'] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['wins']['total']++;
                    }

                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['sprint']['wins'][$season])) {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['wins'][$season] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['wins'][$season]++;
                    }
                }

                if($session == "sprint" && ($teamResult['position'] <= 3)) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['sprint']['podiums']['total'])) {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['podiums']['total'] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['podiums']['total']++;
                    }

                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['sprint']['podiums'][$season])) {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['podiums'][$season] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['podiums'][$season]++;
                    }
                }

                if($session == "sprint" && isset($teamResult['position'])) {
                    if(!isset($teams[$teamResult['team']['teamId']]['stats']['sprint']['positions'][$teamResult['position']])) {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['positions'][$teamResult['position']] = 1;
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['sprint']['positions'][$teamResult['position']]++;
                    }

                    ksort($teams[$teamResult['team']['teamId']]['stats']['sprint']['positions']);
                }

                if($session == "race" || $session == "sprint") {
                    if (!isset($teams[$teamResult['team']['teamId']]['stats']['points']['total'])) {
                        $teams[$teamResult['team']['teamId']]['stats']['points']['total'] = $teamResult['points'];
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['points']['total'] += $teamResult['points'];
                    }

                    if (!isset($teams[$teamResult['team']['teamId']]['stats']['points'][$season])) {
                        $teams[$teamResult['team']['teamId']]['stats']['points'][$season] = $teamResult['points'];
                    } else {
                        $teams[$teamResult['team']['teamId']]['stats']['points'][$season] += $teamResult['points'];
                    }
                }
            }
        }
    }
}

file_put_contents('../data/teams.json', json_encode($teams));