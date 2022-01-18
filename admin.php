<?php
session_start();
if ($_SESSION["loggedInUser"] !== "admin" || !isset($_GET["id"]) || !isset($_GET["teamid"]))
{
    header("Location:index.php");
    exit();
}

function load_from_file($filename, $default_data = [])
{
    $data = @file_get_contents($filename);
    return ($data === false
        ? $default_data
        : json_decode($data, TRUE));
}
$matches = load_from_file("matches.json");
$teams = load_from_file("teams.json");

function save_to_file($filename, $data)
{
    $file_data = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filename, $file_data, LOCK_EX);
}

$homeError = null;
$awayError = null;
$dateError = null;
$errors = 0;
if (isset($_POST["submit"]))
{
    if ((filter_var($_POST["home"], FILTER_VALIDATE_INT) !== 0 && !filter_var($_POST["home"], FILTER_VALIDATE_INT) || $_POST["home"] < 0) && $_POST["home"] !== "")
    {
        $homeError = "A pontnak pozitív egész számnak kell lennie!";
        $errors++;
    }
    if ((filter_var($_POST["away"], FILTER_VALIDATE_INT) !== 0 && !filter_var($_POST["away"], FILTER_VALIDATE_INT) || $_POST["away"] < 0) && $_POST["away"] !== "")
    {
        $awayError = "A pontnak pozitív egész számnak kell lennie!";
        $errors++;
    }
    if ($_POST["home"] == "" && $_POST["away"] !== "")
    {
        $homeError = "Nem lehet csak az egyik mező üres!";
        $errors++;
    }
    if ($_POST["away"] == "" && $_POST["home"] !== "")
    {
        $awayError = "Nem lehet csak az egyik mező üres!";
        $errors++;
    }

    if ($errors === 0)
    {
        $matches[$_GET["id"]] = [
            "id" => $matches[$_GET["id"]]["id"],
            "home" => [
                "id" => $matches[$_GET["id"]]["home"]["id"],
                "score" => $_POST["home"],
            ],
            "away" => [
                "id" => $matches[$_GET["id"]]["away"]["id"],
                "score" => $_POST["away"],
            ],
            "date" => $_POST["date"],
        ];
        save_to_file("matches.json", $matches);
        header("Location: teamdetails.php?id=" . $_GET["teamid"]);
    }
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
    <title>Meccs adatainak módosítása</title>
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
        <h1><?= $teams[$matches[$_GET["id"]]["home"]["id"]]["name"] ?> -<?= $teams[$matches[$_GET["id"]]["away"]["id"]]["name"] ?></h1>
        <hr>
        <form action="" method="post" novalidate>
            <span class="registerName"><?= $teams[$matches[$_GET["id"]]["home"]["id"]]["name"] ?> pontjainak módosítása</span>
            <input class="<?= $homeError == "" ? "" : "errorUnderline" ?>" value="<?= $matches[$_GET["id"]]["home"]["score"] ?>" type="text" name="home"> <br>
            <span class="errorMessage"><?= $homeError == "" ? "" : $homeError ?></span> <br>

            <span class="registerName"><?= $teams[$matches[$_GET["id"]]["away"]["id"]]["name"] ?> pontjainak módosítása</span>
            <input class="<?= $awayError == "" ? "" : "errorUnderline" ?>" value="<?= $matches[$_GET["id"]]["away"]["score"] ?>" type="text" name="away"> <br>
            <span class="errorMessage"><?= $awayError == "" ? "" : $awayError ?></span> <br>

            <span class="registerName">Dátum módosítása</span>
            <input value="<?= $matches[$_GET["id"]]["date"] ?>" type="date" name="date"> <br>

            <input class="button" value="Módosítás" type="submit" name="submit">
        </form>
    </div>
</body>

</html>