<?php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="student-info">Лабораторная работа №6 — Анкета</p>
        </div>
    </header>

    <main class="container">
        <div class="intro">
            <p>Заполните форму. При первой отправке будут сгенерированы логин и пароль.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p>✅ Вы авторизованы как <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>
            <?php else: ?>
                <p>🔐 <a href="login.php">Войдите</a>, чтобы редактировать свои данные</p>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-info">
            <span>👤 <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
            <a href="logout.php" class="logout-btn">🚪 Выйти</a>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['new_login']) && isset($_GET['new_password'])): ?>
        <div class="credentials-box">
            <p><strong>✅ Данные сохранены!</strong></p>
            <p class="login-cred">🔑 Логин: <strong><?php echo htmlspecialchars($_GET['new_login']); ?></strong></p>
            <p class="login-cred">🔒 Пароль: <strong><?php echo htmlspecialchars($_GET['new_password']); ?></strong></p>
            <p style="font-size: 13px; color: #666;">* Сохраните их!</p>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
        <div class="credentials-box" style="border-color:#4caf50;">
            <p><strong>✅ Данные обновлены!</strong></p>
        </div>
        <?php endif; ?>

        <form action="process.php" method="GET" class="application-form">
            <input type="hidden" name="edit_id" value="<?php echo $_SESSION['user_id'] ?? ''; ?>">

            <div class="form-group">
                <label>ФИО <span class="required">*</span></label>
                <input type="text" name="full_name" placeholder="Иванов Иван Иванович" required>
            </div>

            <div class="form-group">
                <label>Телефон <span class="required">*</span></label>
                <input type="tel" name="phone" placeholder="+7 900 123-45-67" required>
            </div>

            <div class="form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" name="email" placeholder="example@mail.ru" required>
            </div>

            <div class="form-group">
                <label>Дата рождения <span class="required">*</span></label>
                <input type="date" name="birth_date" required>
            </div>

            <div class="form-group">
                <label>Пол <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" required> Мужской</label>
                    <label><input type="radio" name="gender" value="female"> Женский</label>
                </div>
            </div>

            <div class="form-group">
                <label>Языки программирования <span class="required">*</span></label>
                <select name="languages[]" multiple size="6" required>
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
                <small>Зажмите <kbd>Ctrl</kbd> (или <kbd>Cmd</kbd> на Mac) для выбора нескольких</small>
            </div>

            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography" rows="4" placeholder="Расскажите о себе..."></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_accepted" value="1" required>
                    С контрактом ознакомлен(а) <span class="required">*</span>
                </label>
            </div>

            <button type="submit" class="submit-btn">✅ Сохранить</button>
        </form>

        <div class="action-buttons">
            <a href="list.php" class="action-btn">📋 Анкеты</a>
            <a href="admin.php" class="action-btn admin">👑 Админ-панель</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="action-btn secondary">🔐 Вход</a>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Лабораторная работа №6 | Май 2026</p>
        </div>
    </footer>
</body>
</html>