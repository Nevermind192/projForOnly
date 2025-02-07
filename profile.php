<?php
session_start();
$connection = mysqli_connect("localhost", "root", "", "projForOnly");

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$query = "SELECT name, email, phone FROM users WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$name = $user["name"] ?? "";
$email = $user["email"] ?? "";
$phone = $user["phone"] ?? "";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $new_name = trim($_POST["name"]);
    $new_email = trim($_POST["email"]);
    $new_phone = trim($_POST["phone"]);
    $current_password = $_POST["current_password"] ?? null;
    $new_password = $_POST["password"] ?? null;
    $confirm_password = $_POST["confirm_password"] ?? null;

    if ($new_name !== $name)
    {
        $stmt = $connection->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $user_id);
        $stmt->execute();
    }

    if ($new_email !== $email)
    {
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL))
        {
            $error = "Некорректный email.";
        } else
        {
            $stmt = $connection->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $new_email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0)
            {
                $error = "Этот email уже используется.";
            }
            else
            {
                $stmt = $connection->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->bind_param("si", $new_email, $user_id);
                $stmt->execute();
            }
        }
    }

    if ($new_phone !== $phone)
    {
        if (!preg_match('/^\+?\d{10,15}$/', $new_phone))
        {
            $error = "Некорректный номер телефона.";
        }
        else
        {
            $stmt = $connection->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
            $stmt->bind_param("si", $new_phone, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0)
            {
                $error = "Этот номер телефона уже используется.";
            }
            else
            {
                $stmt = $connection->prepare("UPDATE users SET phone = ? WHERE id = ?");
                $stmt->bind_param("si", $new_phone, $user_id);
                $stmt->execute();
            }
        }
    }

    if (!empty($new_password) && empty($confirm_password))
    {
        $error = "Подтвердите пароль.";
    }
    elseif (!empty($new_password) && !empty($confirm_password))
    {
        if (empty($current_password))
        {
            $error = "Введите текущий пароль.";
        }
        elseif ($new_password != $confirm_password)
        {
            $error = "Пароли не совпадают.";
        }
        else
        {
            $stmt = $connection->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($db_password);
            $stmt->fetch();
            $stmt->close();

            if (password_verify($current_password, $db_password))
            {
                $error = "Неверный текущий пароль";
            }
            else
            {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $connection->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }
        }
    }

    if (!isset($error))
    {
        $stmt = $connection->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $new_name, $new_email, $new_phone, $user_id);
        if ($stmt->execute())
        {
            $success = "Данные успешно обновлены.";
        }
        else
        {
            $error = "Ошибка обновления данных.";
        }
    }
}
?>

<head>
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="profile.css">
</head>
<header>
    <div class="container">
        <h1>Nevermind</h1>
        <nav>
            <a href="index.php" class="btn">Главная страница</a>
            <a href="logout.php" class="btn">Выйти</a>
        </nav>
    </div>
</header>
<body>
    <div class="wrapper wrapper--profile">
        <h1>Настройки профиля</h1>
        <p>Изменить персональные данные учетной записи</p>
        <div class="form-container">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="name">Имя:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Телефон:</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Эл. почта:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group">
                    <label for="current_password">Текущий пароль:</label>
                    <input type="password" name="current_password" placeholder="Введите текущий пароль">
                </div>
                <div class="form-group">
                    <label for="password">Новый пароль:</label>
                    <input type="password" name="password" placeholder="Введите новый пароль">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Подтверждение пароля:</label>
                    <input type="password" name="confirm_password" placeholder="Повторите новый пароль">
                </div>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
                <button type="submit" class="save-btn">Сохранить изменения</button>
            </form>
        </div>
    </div>
</body>