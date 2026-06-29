<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT id, full_name, password_hash FROM applications WHERE login = ?");
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
<html>
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .container { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { background: #7b1fa2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
        .btn:hover { background: #4a148c; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Вход</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>
        <p style="margin-top:15px;"><a href="index.php">Заполнить анкету</a></p>
    </div>
</body>
</html>