<?php
// Deny direct access to this file
if (basename($_SERVER['PHP_SELF']) == 'functions.php') {
    header('HTTP/1.0 403 Forbidden');
    die();
}
function country_to_code($countryName): string
{
    $country = file_get_contents("data/nationality.json");
    $country = json_decode($country, true);

    return strtolower($country[$countryName]);
}

function curl_get($url): array
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);

    $data = json_decode($output, true);
    curl_close($ch);

    return $data;
}

function color_team($teamId): string
{
    $teamColor = 'team-default';
    switch ($teamId) {
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
        case 'alphatauri':
        {
            $teamColor = 'team-alpha-tauri';
            break;
        }
        case 'alfa':
        {
            $teamColor = 'team-alfa-romeo';
            break;
        }
        case 'default':
        {
            break;
        }
    }

    return $teamColor;
}