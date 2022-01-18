<?php
session_start();
if (!isset($_GET["id"]))
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
$teams = load_from_file("teams.json");
$matches = load_from_file("matches.json");
$comments = load_from_file("comments.json");

function sortByDate($a, $b)
{
    if ($a['date'] == $b['date'])
    {
        return 0;
    }
    return ($a['date'] < $b['date']) ? -1 : 1;
}
uasort($matches, 'sortByDate');

if (isset($_GET["id"]))
{
    $name = $teams[$_GET["id"]]["name"];
    $city = $teams[$_GET["id"]]["city"];
}

function colorScore($value)
{
    if ($value["home"]["id"] === $_GET["id"])
    {
        if ($value["home"]["score"] > $value["away"]["score"])
        {
            return "#638422";
        }
        else if ($value["home"]["score"] == $value["away"]["score"])
        {
            return "#e9c46a";
        }
        else if ($value["home"]["score"] < $value["away"]["score"])
        {
            return "#D54B4B";
        }
    }
    if ($value["away"]["id"] === $_GET["id"])
    {
        if ($value["away"]["score"] > $value["home"]["score"])
        {
            return "#638422";
        }
        else if ($value["away"]["score"] == $value["home"]["score"])
        {
            return "#e9c46a";
        }
        else if ($value["away"]["score"] < $value["home"]["score"])
        {
            return "#D54B4B";
        }
    }
}

$emptyComment = null;
if (isset($_POST["submit"]) && isset($_SESSION["loggedInUser"]))
{
    if ($_POST["comment"] != null)
    {
        $dateNow = date("Y-m-d");
        $timeNow = date("H:i");
        $comment = [
            'author' => $_SESSION["loggedInUser"],
            'text' => $_POST["comment"],
            'teamid' => $_GET["id"],
            'date' => $dateNow,
            'time' => $timeNow,

        ];
        array_push($comments, $comment);
    }
    else
    {
        $emptyComment = "A hozzászólás nem lehet üres!";
    }
}

if (isset($_GET["deletecomment"]) && isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"] === "admin")
{
    unset($comments[$_GET["deletecomment"]]);
    header("Location: teamdetails.php?id=" . $_GET["id"]);
}

function save_to_file($filename, $data)
{
    $file_data = json_encode($data, JSON_PRETTY_PRINT);
    return file_put_contents($filename, $file_data, LOCK_EX);
}
save_to_file("comments.json", $comments);
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
    <title>Csapatrészletek</title>
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
        <h1><?= $name ?></h1>
        <p style="font-size:1.15em;color:#2C817E;font-weight:bold;margin-top:0;">Város: <?= $city ?></p>
        <h2>Meccsek</h2>
        <?php foreach ($matches as $key => $value) : ?>
            <?php if ($value["home"]["id"] === $_GET["id"] || $value["away"]["id"] === $_GET["id"]) : ?>
                <?= $teams[$value["home"]["id"]]["name"] ?> -
                <?= $teams[$value["away"]["id"]]["name"] ?> <br>
                <?php if ($value["home"]["score"] !== "" && $value["home"]["score"] !== "") : ?>
                    Eredmény:
                    <span style="font-weight: bold;color:<?= colorScore($value) ?>"> <?= $value["home"]["score"] ?> </span> -
                    <span style="font-weight: bold;color:<?= colorScore($value) ?>"><?= $value["away"]["score"] ?></span> <br>
                <?php endif; ?>
                Dátum: <?= $value["date"] ?> <br>
                <?php if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"] === "admin") : ?>
                    <a href="admin.php?id=<?= $value["id"] ?>&teamid=<?= $_GET["id"] ?>">Meccs adatának módosítása</a>
                <?php endif; ?>
                <hr class="removeLast">
            <?php endif; ?>
        <?php endforeach; ?>

        <h2>Hozzászólások</h2>
        <form action="" method="post" novalidate>
            <textarea class="<?= $emptyComment == null ? "" : "errorUnderline" ?>" <?= isset($_SESSION["loggedInUser"]) ? "" : "disabled" ?> name="comment" id="" cols="80" rows="15" placeholder="<?= isset($_SESSION["loggedInUser"]) ? "" : "Komment írásához jelentkezz be!" ?>"></textarea> <br>
            <span class="errorMessage"><?= $emptyComment ?></span> <br>
            <?php if (isset($_SESSION["loggedInUser"])) : ?>
                <input class="button" value="Elküldés" type="submit" name="submit"> <br> <br> <br> <br>
            <?php endif; ?>
        </form>

        <?php foreach (array_reverse($comments) as $key => $value) :
            if ($value["teamid"] === $_GET["id"]) : ?>
                <div class="comments">
                    <span style="font-weight:bold;color:#2C817E;"><?= $value["author"] ?></span>
                    <?php if (isset($_SESSION["loggedInUser"]) && $_SESSION["loggedInUser"] === "admin") : ?>
                        <span id="deleteComment"><a href="teamdetails.php?id=<?= $_GET["id"] ?>&deletecomment=<?= array_search($value, $comments) ?>">Törlés</a></span>
                        <br>
                    <?php endif; ?>
                    <span style="font-size: 0.88em;color:#546E7A;"><?= $value["date"] ?>,</span>
                    <span style="font-size: 0.88em;color:#546E7A;"><?= $value["time"] ?></span> <br> <br>
                    <span style="white-space: break-spaces;"><?= $value["text"] ?></span>
                </div>
        <?php endif;
        endforeach; ?>
    </div>
</body>

</html>