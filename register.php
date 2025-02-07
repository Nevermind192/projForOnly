<?php
session_start();
$connection = new mysqli("localhost", "root", "", "projForOnly");

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    if($password != $password_confirmation)
    {
        $error = 'Пароли не совпадают';
    }
    else
    {
        $stmt = $connection->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows != 0)
        {
            $error = "Пользователь с таким email или телефоном уже зарегистрирован!";
        }
        else
        {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $connection->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $phone, $email, $hashed_password);

            if ($stmt->execute())
            {
                $user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                header('Location: profile.php');
            }
            else
            {
                $error = "Ошибка при регистрации.";
            }
        }
    }
}

?>

<head>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="header.css">
</head>
<header>
    <div class="container">
        <h1>Nevermind</h1>
        <nav>
            <a href="index.php" class="btn">Главная страница</a>
        </nav>
    </div>
</header>
<body>
<div class="wrapper">
    <h1>Регистрация</h1>
    <form action="" method="POST">
        <label>Имя</label>
        <div class="input-box">
            <input type="text" name="name" placeholder="Введите имя" required>
        </div>
        <label>Номер телефона</label>
        <div class="input-box">
            <input type="text" name="phone" placeholder="Введите номер телефона" required>
        </div>
        <label>Email</label>
        <div class="input-box">
            <input type="email" name="email" placeholder="Введите адрес электронной почты" required>
        </div>
        <label>Пароль</label>
        <div class="input-box">
            <input type="password" name="password" placeholder="Введите пароль" required>
        </div>
        <div class="input-box">
            <input type="password" name="password_confirmation" placeholder="Введите пароль повторно" required>
        </div>
        <div class="error">
            <?php echo $error ?>
        </div>
        <button class="btn" type="submit">Зарегистрироваться</button>
        <div class="login-link">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </form>
</div>
</body>
