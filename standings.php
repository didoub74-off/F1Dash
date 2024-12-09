<?php
require 'functions.php';

if (isset($_GET['season'])) {
    $seasonNumber = $_GET['season'];
} else {
    $seasonNumber = 2024;
}
$recap = json_decode(file_get_contents('data/recap.json'), true)[$seasonNumber];

$standings = $recap['driverChampionship'];

$constructorStanding = $recap['teamChampionship'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>F1 Database - Standings</title>
    <link rel="stylesheet" type="text/css" href="styles/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css"/>
</head>
<body>
<div class="navBar">
    <div class="websiteName">
        <h1>F1 Database</h1>
    </div>
    <div class="navChoices">
        <h4 class="pages"><a href="index.php" class="pages">Home</a></h4>
        <h4 class="pages"><a href="seasons.php" class="pages">Seasons</a></h4>
        <h4 class="pages"><a href="standings.php" class="currentPage pages">Standings</a></h4>
        <h4 class="pages"><a href="races.php" class="pages">Races</a></h4>
        <h4 class="pages"><a href="drivers.php" class="pages">Drivers</a></h4>
        <h4 class="pages"><a href="teams.php" class="pages">Teams</a></h4>
    </div>
</div>
<div class="welcomeBanner">
    <h1>Formula 1 Database - Standings</h1>
</div>
<div class="standingsHeader">
    <div class="previousButton disabled" onclick="previousButton()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512">
            <!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path d="M9.4 278.6c-12.5-12.5-12.5-32.8 0-45.3l128-128c9.2-9.2 22.9-11.9 34.9-6.9s19.8 16.6 19.8 29.6l0 256c0 12.9-7.8 24.6-19.8 29.6s-25.7 2.2-34.9-6.9l-128-128z"/>
        </svg>
    </div>
    <div class="selectDivSeasonStanding">
        <label>
            <select class="selectSeasonStanding">
                <option disabled selected>Choose a season</option>
                <?php
                $seasonList = array_keys(json_decode(file_get_contents('data/recap.json'), true));
                foreach ($seasonList as $season) {
                    echo "<option value='$season'>$season</option>";
                }
                ?>
            </select>
        </label>
    </div>
    <div class="nextButton" onclick="nextButton()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512">
            <!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path d="M246.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-128-128c-9.2-9.2-22.9-11.9-34.9-6.9s-19.8 16.6-19.8 29.6l0 256c0 12.9 7.8 24.6 19.8 29.6s25.7 2.2 34.9-6.9l128-128z"/>
        </svg>
    </div>
</div>
<div class="standingsContainer">
    <div class="slider">
        <div class="driverChampionship">
            <h2 class="standingTitle">Drivers' Championship</h2>
            <table class="table-container">
                <thead>
                <tr class="table-header">
                    <th class="driverInfo">Driver</th>
                    <th class="teamInfo">Constructor</th>
                    <th class="victoryInfo">Victories</th>
                    <th class="poleInfo">Pole</th>
                    <th class="pointsInfo">Points</th>
                </thead>
                <tbody>
                <?php
                foreach ($standings['drivers_championship'] as $driver) {
                    $teamColor = '';
                    if ($seasonNumber < 2021) {
                        $teamColor = 'team-default';
                    } else {
                        $teamColor = color_team($driver['team']['teamId']);
                    }
                    $countryCode = country_to_code($driver['driver']['nationality']);

                    $victories = 0;
                    if (isset($recap['stats']['winner']['driver'][$driver['driver']['driverId']])) {
                        $victories = $recap['stats']['winner']['driver'][$driver['driver']['driverId']];
                    }

                    $pole = 0;
                    if (isset($recap['stats']['pole']['driver'][$driver['driver']['driverId']])) {
                        $pole = $recap['stats']['pole']['driver'][$driver['driver']['driverId']];
                    }
                    ?>
                    <tr class="table-content">
                        <td class="driverInfo">
                            <span class="position <?= $teamColor ?>"><?php echo $driver['position']; ?></span>
                            <span class="fi fi-<?= $countryCode ?>"></span>
                            <span class="driver-name"><?php echo $driver['driver']['name'] ?></span>
                            <span class="driver-surname"><?php echo $driver['driver']['surname'] ?></span>
                        </td>
                        <td class="teamInfo">
                            <span class="team"><?php echo $driver['team']['teamName']; ?></span>
                        </td>
                        <td class="victoryInfo">
                            <span class="victories"><?= $victories ?></span>
                        </td>
                        <td class="poleInfo">
                            <span class="pole"><?= $pole ?></span>
                        </td>
                        <td class="pointsInfo">
                            <span class="points"><?php echo $driver['points']; ?></span>
                            <i class="fas fa-chevron-right"></i>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="constructorChampionship">
            <h2 class="standingTitle">Constructors' Championship</h2>
            <table class="table-container">
                <thead>
                <tr>
                    <th class="constructorInfo">Constructor</th>
                    <th class="victoryInfo">Victories</th>
                    <th class="poleInfo">Pole</th>
                    <th class="pointsInfo">Points</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($constructorStanding['constructors_championship'] as $constructor) {
                    $teamColor = '';
                    if ($seasonNumber < 2021) {
                        $teamColor = 'team-default';
                    } else {
                        $teamColor = color_team($constructor['team']['teamId']);
                    }

                    if ($constructor['position'] >= 10) {
                        $moreThanTen = 'position-more-10';
                    } else {
                        $moreThanTen = '';
                    }

                    $victories = 0;
                    if (isset($recap['stats']['winner']['team'][$constructor['team']['teamId']])) {
                        $victories = $recap['stats']['winner']['team'][$constructor['team']['teamId']];
                    }

                    $pole = 0;
                    if (isset($recap['stats']['pole']['team'][$constructor['team']['teamId']])) {
                        $pole = $recap['stats']['pole']['team'][$constructor['team']['teamId']];
                    }

                    $countryCode = country_to_code($constructor['team']['country']);
                    ?>
                    <tr class="table-content constructorStanding">
                        <td class="constructorInfo">
                            <span class="position <?= $teamColor ?>"><?php echo $constructor['position']; ?></span>
                            <span class="fi fi-<?= $countryCode ?>"></span>
                            <span class="driver-name"><?php echo $constructor['team']['teamName'] ?></span>
                        </td>
                        <td class="victoryInfo">
                            <span class="victories"><?= $victories ?></span>
                        </td>
                        <td class="poleInfo">
                            <span class="pole"><?= $pole ?></span>
                        </td>
                        <td class="pointsInfo">
                            <span class="points"><?php echo $constructor['points']; ?></span>
                            <i class="fas fa-chevron-right"></i>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    const slider = document.querySelector('.slider');
    const prevBtn = document.querySelector('.previousButton');
    const nextBtn = document.querySelector('.nextButton');
    const select = document.querySelector('select');

    select.addEventListener('change', () => {
        window.location.href = `standings.php?season=${select.value}`;
    });
    let currentSlide = 0;

    const updateSlide = () => {
        slider.style.transform = `translateX(-${currentSlide * 50}%)`;
    }

    function previousButton() {
        if (currentSlide > 0) {
            currentSlide--;
            updateSlide();
            prevBtn.classList.add('disabled');
            nextBtn.classList.remove('disabled');
        }
    }

    function nextButton() {
        if (currentSlide < 1) {
            currentSlide++;
            updateSlide();
            prevBtn.classList.remove('disabled');
            nextBtn.classList.add('disabled');
        }
    }
</script>
<div class="footer">
    <h4>© 2024 F1 Database by Didoub74</h4>
    <p>This website is not affiliated in any way with Formula One. This is only a personal project to practice
        coding.</p>
</div>
</body>
</html>