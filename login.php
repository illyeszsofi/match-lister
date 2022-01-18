<?php
session_start();
if (isset($_SESSION["loggedInUser"]))
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
$users = load_from_file("users.json");

$registerError = null;
$registeredFound = false;
if (isset($_POST["submit"]))
{
    if ($_POST["username"] !== "" && $_POST["password"] !== "")
    {
        foreach ($users as $key => $value)
        {
            if ($value["username"] === $_POST["username"] && $value["password"] === $_POST["password"])
            {
                $_SESSION["loggedInUser"] = $_POST["username"];
                $registeredFound = true;
                header("Location: index.php");
            }
        }
        if (!$registeredFound)
        {
            $registerError = "Hibás felhasználónév vagy jelszó!";
        }
    }
    else
    {
        $registerError = "A mezők kitöltése kötelező!";
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
    <title>Bejelentkezés</title>
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

    <div class="content mid">
        <h1>Bejelentkezés</h1>
        <hr>
        <form action="" method="post" novalidate>
            <span class="registerName">Felhasználónév:</span>
            <input class="<?= $registerError == "" ? "" : "errorUnderline" ?>" type="text" name="username" placeholder="Felhasználónév"> <br>

            <span class="registerName">Jelszó:</span>
            <input class="<?= $registerError == "" ? "" : "errorUnderline" ?>" type="password" name="password" placeholder="Jelszó"> <br>
            <span class="errorMessage"><?= $registerError ?></span> <br>

            <input class="button" value="Bejelentkezés" type="submit" name="submit">
        </form>
    </div>
</body>

</html>