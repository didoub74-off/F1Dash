<?php
$data = file_get_contents('data/seasons.json');
$keys = array_keys(json_decode($data, true));

// Order the seasons by year in descending order
rsort($keys);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>F1 Database - Season history</title>
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
        <h4 class="pages"><a href="seasons.php" class="currentPage pages">Seasons</a></h4>
        <h4 class="pages"><a href="standings.php" class="pages">Standings</a></h4>
    </div>
</div>
<div class="welcomeBanner">
    <h1>Formula 1 Database - Seasons History</h1>
</div>
<div>
    <table class="pastSeasonTable">
        <thead>
        <tr>
            <th>Season</th>
            <th>Driver champion</th>
            <th>Constructor champion</th>
            <th>Races</th>
            <th>Drivers</th>
            <th>Constructors</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($keys as $key) : ?>
            <?php $season = json_decode($data, true)[$key]; ?>
            <tr>
                <td><?= $key ?></td>
                <td><?= $season['driver']['driver']['name'] . " " . $season['driver']['driver']['surname'] ?></td>
                <td><?= $season['team']['team']['teamName'] ?></td>
                <td><?= $season['numbers']['races'] ?></td>
                <td><?= $season['numbers']['drivers'] ?></td>
                <td><?= $season['numbers']['teams'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="footer">
    <h4>© 2024 F1 Database by Didoub74</h4>
    <p>This website is not affiliated in any way with Formula One. This is only a personal project to practice
        coding.</p>
</div>
</body>
</html>