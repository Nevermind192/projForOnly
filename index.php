<?php
session_start();
?>

<head>
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="header.css">
</head>
<header>
    <div class="container">
        <h1>Nevermind</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="btn">Профиль</a>
                <a href="logout.php" class="btn">Выйти</a>
            <?php else: ?>
                <a href="register.php" class="btn">Регистрация</a>
                <a href="login.php" class="btn">Войти</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<body>

</body>