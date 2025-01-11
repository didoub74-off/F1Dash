<?php

$data = json_decode(file_get_contents('2024Session.json'), true);

foreach ($data as $meeting) {
    $meetingId = $meeting['id'];
    foreach ($meeting['practice_sessions'] as $session) {
        $sessionId = $session['id'];

        print "Working on practice $sessionId" . PHP_EOL;


        $link = $session['url'];
        // Use script getLapsTime.php, with arg 1 as the link and arg 2 as the sessionId

        print shell_exec("php getLapsTime.php $link $sessionId");

        // Use getResultsTime.php, with arg 1 as the sessionId

        print shell_exec("php getResultsTime.php $sessionId");
    }

    foreach ($meeting['qualifying_sessions'] as $session) {
        $sessionId = $session['id'];

        print "Working on qualifying $sessionId" . PHP_EOL;

        $link = $session['url'];
        // Use script getLapsTime.php, with arg 1 as the link and arg 2 as the sessionId

        print shell_exec("php getQualifyingTime.php $link $sessionId");

        // Use getResultsQualifying.php, with arg 1 as the sessionId

        print shell_exec("php getResultsQualifying.php $sessionId");
    }

    foreach($meeting['race_sessions'] as $session) {
        $sessionId = $session['id'];

        print "Working on race $sessionId" . PHP_EOL;

        $link = $session['url'];

        $finalPosition = $session['final_position'];
        // Use script getLapsTime.php, with arg 1 as the link and arg 2 as the sessionId

        print shell_exec("php getLapsTime.php $link $sessionId");

        // Use getResultsTime.php, with arg 1 as the sessionId

        print shell_exec("php getResultsRace.php $finalPosition $sessionId");
    }
}