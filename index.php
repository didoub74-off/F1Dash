<?php
require 'functions.php';

$standings = curl_get('https://f1connectapi.vercel.app/api/current/drivers-championship');

$nextRace = curl_get('https://f1connectapi.vercel.app/api/current/next');

$lastRace = curl_get('https://f1connectapi.vercel.app/api/current/last/race')['races'];

if(isset($nextRace['message']) && $nextRace['message'] == 'No race found for this round. Try with other one.') {
    $nextRace = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>F1 Database</title>
    <link rel="stylesheet" type="text/css" href="styles/main.css">
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"
    />
</head>
<body>
<div class="navBar">
    <div class="websiteName">
        <h1>F1 Database</h1>
    </div>
    <div class="navChoices">
        <h4 class="pages"><a href="index.php" class="currentPage pages">Home</a></h4>
        <h4 class="pages"><a href="seasons.php" class="pages">Seasons</a></h4>
        <h4 class="pages"><a href="standings.php" class="pages">Standings</a></h4>
        <h4 class="pages"><a href="races.php" class="pages">Races</a></h4>
        <h4 class="pages"><a href="drivers.php" class="pages">Drivers</a></h4>
        <h4 class="pages"><a href="teams.php" class="pages">Teams</a></h4>
    </div>
</div>
<div class="welcomeBanner">
    <h1>Formula 1 Database by <span style="color: red">Didoub74</span></h1>
</div>
<?php if($nextRace != null) { ?>
<div class="nextRace">
    <h1 class="title">Next session</h1>
    <?php
    $race = $nextRace['race'][0]['schedule']['race'];
    // Format the date from $nextRace['race']['schedule']['race']['date'];
    $dateTime = date_create($race['date'] . ' ' . $race['time']);
    $date = date_format($dateTime, 'F jS Y');
    $time = date_format($dateTime, 'H:i');

    $countryCode = country_to_code($nextRace['race'][0]['circuit']['country']);
    ?>
    <div class="nextRaceInfo">
        <span class="fi fi-<?= $countryCode ?>"></span>
        <span class="raceName"><?php echo $nextRace['race'][0]['raceName']; ?></span>
        <span style="color:#3ca4f5;">|</span>
        <span class="raceLocation"><?php echo $nextRace['race'][0]['circuit']['circuitName']; ?></span>
        <span style="color:#3ca4f5;">|</span>
        <span class="date"><?php echo $date . " at " . $time . " UTC"; ?></span>
    </div>
</div>
<?php } ?>
<div class="columns">
    <div class="container">
        <div class="standing">
            <h2 class="title columnTitle">Standing</h2>
            <a href="standings.php" class="standingLink">
                <h5 class="summaryTitle">
                    Summary
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512" style="height: 20px; vertical-align: middle"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M246.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-9.2-9.2-22.9-11.9-34.9-6.9s-19.8 16.6-19.8 29.6l0 256c0 12.9 7.8 24.6 19.8 29.6s25.7 2.2 34.9-6.9l128-128z"/></svg>
                </h5>
            </a>
        </div>
        <table class="table-container">
            <?php
            $i = 0;
            foreach ($standings['drivers_championship'] as $driver) {
                if(++$i > 10) {
                    break;
                }
                $teamColor = '';
                switch ($driver['team']['teamId']) {
                    case 'red_bull':
                        $teamColor = 'team-red-bull-racing';
                        break;
                    case 'mercedes':
                        $teamColor = 'team-mercedes';
                        break;
                    case 'mclaren':
                        $teamColor = 'team-mclaren';
                        break;
                    case 'ferrari':
                        $teamColor = 'team-ferrari';
                        break;
                    case 'aston_martin':
                        $teamColor = 'team-aston-martin';
                        break;
                    case 'alpine':
                        $teamColor = 'team-alpine';
                        break;
                    case 'rb':
                    {
                        $teamColor = 'team-racing-bull';
                        break;
                    }
                    case 'haas':
                    {
                        $teamColor = 'team-haas';
                        break;
                    }
                    case 'williams':
                    {
                        $teamColor = 'team-williams';
                        break;
                    }
                    case 'sauber':
                    {
                        $teamColor = 'team-kick-sauber';
                        break;
                    }
                }

                if ($driver['position'] >= 10) {
                    $moreThanTen = 'position-more-10';
                } else {
                    $moreThanTen = '';
                }

                $countryCode = country_to_code($driver['driver']['nationality']);
                ?>
                <tr class="table-content">
                    <td class="<?= $moreThanTen ?> driverInfo">
                        <span class="position <?= $teamColor ?>"><?php echo $driver['position']; ?></span>
                        <span class="fi fi-<?= $countryCode ?>"></span>
                        <span class="driver-name"><?php echo $driver['driver']['name'] ?></span>
                        <span class="driver-surname"><?php echo $driver['driver']['surname'] ?></span>
                    </td>
                    <td class="teamInfo">
                        <span class="team"><?php echo $driver['team']['teamName']; ?></span>
                    </td>
                    <td class="pointsInfo">
                        <span class="points"><?php echo $driver['points']; ?></span>
                        <i class="fas fa-chevron-right"></i>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
    <div class="container">
        <h2 class="title columnTitle">Last race</h2>
        <div class="previousRace">
            <h2>Qatar Grand Prix</h2>
            <div class="date-time">
                <div class="date">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="dateTimeIcon"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#0270c5" d="M152 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40L64 64C28.7 64 0 92.7 0 128l0 16 0 48L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-256 0-48 0-16c0-35.3-28.7-64-64-64l-40 0 0-40c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40L152 64l0-40zM48 192l352 0 0 256c0 8.8-7.2 16-16 16L64 464c-8.8 0-16-7.2-16-16l0-256z"/></svg>
                    Dec 1, 2021
                </div>
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="dateTimeIcon"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#0270c5" d="M464 256A208 208 0 1 1 48 256a208 208 0 1 1 416 0zM0 256a256 256 0 1 0 512 0A256 256 0 1 0 0 256zM232 120l0 136c0 8 4 15.5 10.7 20l96 64c11 7.4 25.9 4.4 33.3-6.7s4.4-25.9-6.7-33.3L280 243.2 280 120c0-13.3-10.7-24-24-24s-24 10.7-24 24z"/></svg>
                    15:00 UTC
                </div>
            </div>
            <table class="lastRaceResult">
                <?php
                $lastRaceResults = $lastRace['results'];
                usort($lastRaceResults, function ($a, $b) {
                    return $a['position'] <=> $b['position'];
                });
                $i = 0;
                foreach ($lastRaceResults as $driver) {
                    if(++$i > 10) {
                        break;
                    }
                    $countryCode = country_to_code($driver['driver']['birthday']);

                    ?>
                    <tr class="">
                        <td class="">
                            <span class=""><?php echo $driver['position']; ?></span>
                        </td>
                        <td>
                            <span class="fi fi-<?= $countryCode ?>"></span>
                        </td>
                        <td>
                            <span class="driver-name"><?php echo $driver['driver']['surname'] ?></span>
                            <span class="driver-surname"><?php echo $driver['driver']['nationality'] ?></span>
                        </td>
                        <td class="lastRaceTime">
                            <span><?php echo $driver['time']; ?></span>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
<div class="footer">
    <h4>© 2024 F1 Database by Didoub74</h4>
    <p>This website is not affiliated in any way with Formula One. This is only a personal project to practice coding.</p>
</div>
</body>
</html>