<?php
session_start();
function load_from_file($filename, $default_data = [])
{
    $data = @file_get_contents($filename);
    return ($data === false
        ? $default_data
        : json_decode($data, TRUE));
}
$teams = load_from_file("teams.json");
$matches = load_from_file("matches.json");

function sortByDate($a, $b)
{
    if ($a['date'] == $b['date'])
    {
        return 0;
    }
    return ($a['date'] < $b['date']) ? 1 : -1;
}
uasort($matches, 'sortByDate');
$printed = 0;

if (isset($_GET["logout"]))
{
    unset($_SESSION["loggedInUser"]);
    header("Location: index.php");
}
?>


<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300&display=swap" rel="stylesheet">
    <title>Listaoldal</title>
</head>

<body>
    <div class="menu">
        <ul>
            <li><a href="index.php">Eötvös Loránd Stadion</a></li>
            <?php if (isset($_SESSION["loggedInUser"])) : ?>
                <li> <a href="index.php?logout">Kijelentkezés</a></li>
                <li class="menuLoggedInAs" style="float:right;">Bejelentkezve: <?= $_SESSION["loggedInUser"] ?></li>
            <?php else : ?>
                <li><a href="register.php">Regisztráció</a></li>
                <li><a href="login.php">Bejelentkezés</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="content">
        <h1>Eötvös Loránd Stadion</h1>
        <p id="p-less">Kövesd nyomon kedvenc csapataid meccseit! <br> Itt megtekintheted az összes nálunk játszott meccset, hozzászólásban pedig kifejezheted róluk a véleményedet.</p>

        <h2>Csapatok</h2>
        <ul>
            <?php foreach ($teams as $key => $value) : ?>
                <li> <a href="teamdetails.php?id=<?= $value["id"] ?>"><?= $value["name"] ?></a> </li>
            <?php endforeach; ?>
        </ul>

        <h2>Legutóbbi meccsek</h2>
        <ul>
            <?php foreach ($matches as $key => $value) :
                if ($value["home"]["score"] !== "" || $value["away"]["score"] !== "") :
                    $printed++;
            ?>
                    <li>
                        <?= $teams[$value["home"]["id"]]["name"] ?> -
                        <?= $teams[$value["away"]["id"]]["name"] ?> <br>
                        Eredmény: <?= $value["home"]["score"] ?> - <?= $value["away"]["score"] ?> <br>
                        Dátum: <?= $value["date"] ?>
                    </li>
                    <hr class="removeLast">
                <?php endif; ?>
                <?php if ($printed == 5) break; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <br>
</body>

</html>