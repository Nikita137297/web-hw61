<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Анкета</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .container { max-width: 600px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { background: #4caf50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #388e3c; }
        .error { color: red; font-size: 14px; }
        .success { background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .nav { margin-top: 20px; }
        .nav a { margin-right: 10px; color: #7b1fa2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Анкета</h1>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="success">✅ Вы авторизованы как <strong><?php echo $_SESSION['user_name']; ?></strong></div>
            <a href="logout.php">Выйти</a>
        <?php endif; ?>

        <?php if (isset($_GET['new_login'])): ?>
            <div class="success">
                <strong>✅ Данные сохранены!</strong><br>
                Логин: <?php echo $_GET['new_login']; ?><br>
                Пароль: <?php echo $_GET['new_password']; ?>
            </div>
        <?php endif; ?>

        <form action="process.php" method="GET">
            <div class="form-group">
                <label>ФИО *</label>
                <input type="text" name="full_name" required>
            </div>

            <div class="form-group">
                <label>Телефон *</label>
                <input type="text" name="phone" required>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Дата рождения *</label>
                <input type="date" name="birth_date" required>
            </div>

            <div class="form-group">
                <label>Пол *</label>
                <input type="radio" name="gender" value="male" required> Мужской
                <input type="radio" name="gender" value="female"> Женский
            </div>

            <div class="form-group">
                <label>Языки *</label>
                <select name="languages[]" multiple required>
                    <option value="Pascal">Pascal</option>
                    <option value="C">C</option>
                    <option value="C++">C++</option>
                    <option value="JavaScript">JavaScript</option>
                    <option value="PHP">PHP</option>
                    <option value="Python">Python</option>
                    <option value="Java">Java</option>
                    <option value="Haskell">Haskell</option>
                    <option value="Clojure">Clojure</option>
                    <option value="Prolog">Prolog</option>
                    <option value="Scala">Scala</option>
                    <option value="Go">Go</option>
                </select>
                <small>Зажмите Ctrl для выбора нескольких</small>
            </div>

            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="contract_accepted" value="1" required>
                    С контрактом ознакомлен *
                </label>
            </div>

            <button type="submit" class="btn">✅ Сохранить</button>
        </form>

        <div class="nav">
            <a href="list.php">📋 Анкеты</a>
            <a href="admin.php">👑 Админ-панель</a>
        </div>
    </div>
</body>
</html>