<?php
session_start();
if (isset($_SESSION["loggedInUser"]))
{
    header("Location:index.php");
    exit();
}

$username_error = null;
$email_error = null;
$password_error = null;
$passwordAgain_error = null;
$errors = 0;
if (isset($_POST["submit"]))
{
    if (empty($_POST["username"]) && $_POST["username"] !== "0")
    {
        $username_error = "A mező kitöltése kötelező!";
        $errors++;
    }

    if (empty($_POST["email"]) && $_POST["email"] !== "0")
    {
        $email_error = "A mező kitöltése kötelező!";
        $errors++;
    }
    else if (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL))
    {
        $email_error = "Helytelen e-mail cím!";
        $errors++;
    }

    if (empty($_POST["password"]) && $_POST["password"] !== "0")
    {
        $password_error = "A mező kitöltése kötelező!";
        $errors++;
    }
    if (empty($_POST["passwordAgain"]) && $_POST["passwordAgain"] !== "0")
    {
        $passwordAgain_error = "A mező kitöltése kötelező!";
        $errors++;
    }
    else if ($_POST["password"] !== $_POST["passwordAgain"])
    {
        $passwordAgain_error = "A megadott jelszavak nem egyeznek!";
        $errors++;
    }


    function load_from_file($filename, $default_data = [])
    {
        $data = @file_get_contents($filename);
        return ($data === false
            ? $default_data
            : json_decode($data, TRUE));
    }
    $users = load_from_file("users.json");

    foreach ($users as $key => $value)
    {
        if ($value["username"] === $_POST["username"])
        {
            $username_error = "A felhasználó már létezik!";
            $errors++;
        }
    }

    if ($errors === 0)
    {
        $user = [
            'username' => $_POST["username"],
            'email' => $_POST["email"],
            'password' => $_POST["password"],
        ];
        array_push($users, $user);
        header("Location: login.php");
    }

    function save_to_file($filename, $data)
    {
        $file_data = json_encode($data, JSON_PRETTY_PRINT);
        return file_put_contents($filename, $file_data, LOCK_EX);
    }
    save_to_file("users.json", $users);
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
    <title>Regisztráció</title>
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
        <h1>Regisztráció</h1>
        <hr>
        <form action="" method="post" novalidate>
            <span class="registerName">Felhasználónév:</span>
            <input class="<?= $username_error == "" ? "" : "errorUnderline" ?>" value="<?= $_POST["username"] ?? "" ?>" type="text" name="username" placeholder="Felhasználónév"> <br>
            <span class="errorMessage"><?= $username_error == "" ? "" : $username_error ?></span> <br>

            <span class="registerName">E-mail cím:</span>
            <input class="<?= $email_error == "" ? "" : "errorUnderline" ?>" value="<?= $_POST["email"] ?? "" ?>" type="text" name="email" placeholder="E-mail cím"> <br>
            <span class="errorMessage"><?= $email_error == "" ? "" : $email_error ?></span> <br>

            <span class="registerName">Jelszó:</span>
            <input class="<?= $password_error == "" ? "" : "errorUnderline" ?>" value="<?= $_POST["password"] ?? "" ?>" type="password" name="password" placeholder="Jelszó"><br>
            <span class="errorMessage"><?= $password_error == "" ? "" : $password_error ?></span> <br>

            <span class="registerName">Jelszó megerősítése:</span>
            <input class="<?= $passwordAgain_error == "" ? "" : "errorUnderline" ?>" value="<?= $_POST["passwordAgain"] ?? "" ?>" type="password" name="passwordAgain" placeholder="Jelszó ismét"> <br>
            <span class="errorMessage"> <?= $passwordAgain_error == "" ? "" : $passwordAgain_error ?></span> <br>

            <input class="button" value="Regisztráció" type="submit" name="submit">
        </form>
    </div>
</body>

</html>