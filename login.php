<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    
    global $pdo, $table_apps;
    $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM $table_apps WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        header('Location: index.php');
        exit;
    }
    $error = "Неверный логин или пароль";
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="student-info">🔐 Вход в систему</p>
        </div>
    </header>

    <main class="container">
        <div class="admin-login-form">
            <h1>🔐 Вход</h1>
            
            <?php if (isset($error)): ?>
                <div class="error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Логин</label>
                    <input type="text" name="login" placeholder="Введите логин" required>
                </div>
                
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" placeholder="Введите пароль" required>
                </div>
                
                <button type="submit" class="btn">🔑 Войти</button>
            </form>
            
            <p style="text-align:center; margin-top:1.5rem;">
                <a href="index.php" style="color:#7b1fa2; font-weight:600; text-decoration:none;">📝 Заполнить анкету</a>
            </p>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Лабораторная работа №6 | Май 2026</p>
        </div>
    </footer>
</body>
</html>