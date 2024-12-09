<?php
require '../functions.php';

$drivers = [];

$meetings = json_decode(file_get_contents('../data/meetings.json'), true);
for($i=2021; $i<=2024; $i++) {
    $season = $i;
    $seasonMeetings = $meetings[$season];

    foreach ($seasonMeetings as $meeting) {

        // Foreach each $meeting['results'] child (fp1, fp2, fp3, qualy, sprintQualy, race, sprint)
        foreach ($meeting['results'] as $session => $result) {
            if(empty($result)) {
               continue;
            }
            foreach ($result['results'] as $driverResult) {
                $driverData = $driverResult['driver'];
                if(!isset($drivers[$driverData['driverId']])) {
                    $drivers[$driverData['driverId']] = [
                        'driverId' => $driverData['driverId'],
                        'driverName' => $driverData['name'],
                        'driverSurname' => $driverData['surname'],
                        'driverNationality' => $driverData['nationality'],
                        'driverBirthdate' => $driverData['birthday'],
                        'driverNumber' => $driverData['number'],
                        'driverCode' => $driverData['shortName'],
                        'stats' => [
                            'appearances' => []
                        ]
                    ];
                }
                if(!isset($drivers[$driverData['driverId']]['stats']['appearances'][$session])) {
                    $drivers[$driverData['driverId']]['stats']['appearances'][$session] = 1;
                }else {
                    $drivers[$driverData['driverId']]['stats']['appearances'][$session]++;
                }

                if($session == "race" && $driverResult['position'] == 1) {
                    // Count the total wins with ['wins']['total']
                    if(!isset($drivers[$driverData['driverId']]['stats']['race']['wins']['total'])) {
                        $drivers[$driverData['driverId']]['stats']['race']['wins']['total'] = 1;
                    } else {
                        $drivers[$driverData['driverId']]['stats']['race']['wins']['total']++;
                    }

                    // Count the win per season
                    if(!isset($drivers[$driverData['driverId']]['stats']['race']['wins'][$season])) {
                        $drivers[$driverData['driverId']]['stats']['race']['wins'][$season] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['race']['wins'][$season]++;
                    }
                }

                // Count the exact position for each driver
                if($session == "race" && isset($driverResult['position'])) {
                    if(!isset($drivers[$driverData['driverId']]['stats']['race']['positions'][$driverResult['position']])) {
                        $drivers[$driverData['driverId']]['stats']['race']['positions'][$driverResult['position']] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['race']['positions'][$driverResult['position']]++;
                    }

                    // Order the position keys
                    ksort($drivers[$driverData['driverId']]['stats']['race']['positions']);
                }


                if($session == "race" && $driverResult['position'] <= 3) {
                    // Count the total podiums with ['podiums']['total']
                    if(!isset($drivers[$driverData['driverId']]['stats']['race']['podiums']['total'])) {
                        $drivers[$driverData['driverId']]['stats']['race']['podiums']['total'] = 1;
                    } else {
                        $drivers[$driverData['driverId']]['stats']['race']['podiums']['total']++;
                    }

                    // Count the podiums per season
                    if(!isset($drivers[$driverData['driverId']]['stats']['race']['podiums'][$season])) {
                        $drivers[$driverData['driverId']]['stats']['race']['podiums'][$season] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['race']['podiums'][$season]++;
                    }
                }

                if($session == "qualy" && $driverResult['Grid_Position'] == 1) {
                    // Count the total poles with ['poles']['total']
                    if(!isset($drivers[$driverData['driverId']]['stats']['race']['poles']['total'])) {
                        $drivers[$driverData['driverId']]['stats']['race']['poles']['total'] = 1;
                    } else {
                        $drivers[$driverData['driverId']]['stats']['race']['poles']['total']++;
                    }

                    // Count the poles per season
                    if(!isset($drivers[$driverData['driverId']]['stats']['race']['poles'][$season])) {
                        $drivers[$driverData['driverId']]['stats']['race']['poles'][$season] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['race']['poles'][$season]++;
                    }
                }

                if($session == "sprint" && $driverResult['position'] == 1) {
                    if(!isset($drivers[$driverData['driverId']]['stats']['sprint']['wins']['total'])) {
                        $drivers[$driverData['driverId']]['stats']['sprint']['wins']['total'] = 1;
                    } else {
                        $drivers[$driverData['driverId']]['stats']['sprint']['wins']['total']++;
                    }

                    // Count the sprint wins per season
                    if(!isset($drivers[$driverData['driverId']]['stats']['sprint']['wins'][$season])) {
                        $drivers[$driverData['driverId']]['stats']['sprint']['wins'][$season] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['sprint']['wins'][$season]++;
                    }
                }

                if($session == "sprint" && $driverResult['position'] <= 3) {
                    if(!isset($drivers[$driverData['driverId']]['stats']['sprint']['podiums']['total'])) {
                        $drivers[$driverData['driverId']]['stats']['sprint']['podiums']['total'] = 1;
                    } else {
                        $drivers[$driverData['driverId']]['stats']['sprint']['podiums']['total']++;
                    }

                    // Count the sprint wins per season
                    if(!isset($drivers[$driverData['driverId']]['stats']['sprint']['podiums'][$season])) {
                        $drivers[$driverData['driverId']]['stats']['sprint']['podiums'][$season] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['sprint']['podiums'][$season]++;
                    }
                }

                if($session == "sprint" && isset($driverResult['position'])) {
                    if(!isset($drivers[$driverData['driverId']]['stats']['sprint']['positions'][$driverResult['position']])) {
                        $drivers[$driverData['driverId']]['stats']['sprint']['positions'][$driverResult['position']] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['sprint']['positions'][$driverResult['position']]++;
                    }

                    // Order the position keys
                    ksort($drivers[$driverData['driverId']]['stats']['sprint']['positions']);
                }

                if($session == "sprintQualy" && $driverResult['Grid_Position'] == 1) {
                    if(!isset($drivers[$driverData['driverId']]['stats']['sprint']['poles']['total'])) {
                        $drivers[$driverData['driverId']]['stats']['sprint']['poles']['total'] = 1;
                    } else {
                        $drivers[$driverData['driverId']]['stats']['sprint']['poles']['total']++;
                    }

                    // Count the sprint poles per season
                    if(!isset($drivers[$driverData['driverId']]['stats']['sprint']['poles'][$season])) {
                        $drivers[$driverData['driverId']]['stats']['sprint']['poles'][$season] = 1;
                    }else {
                        $drivers[$driverData['driverId']]['stats']['sprint']['poles'][$season]++;
                    }
                }

                // Count the points for each driver
                if($session == "race" || $session == "sprint") {
                    if(!isset($drivers[$driverData['driverId']]['stats']['points']['total'])) {
                        $drivers[$driverData['driverId']]['stats']['points']['total'] = $driverResult['points'];
                    } else {
                        $drivers[$driverData['driverId']]['stats']['points']['total'] += $driverResult['points'];
                    }

                    if(!isset($drivers[$driverData['driverId']]['stats']['points'][$season])) {
                        $drivers[$driverData['driverId']]['stats']['points'][$season] = $driverResult['points'];
                    }else {
                        $drivers[$driverData['driverId']]['stats']['points'][$season] += $driverResult['points'];
                    }
                }
            }
        }
    }
}

file_put_contents('../data/drivers.json', json_encode($drivers));