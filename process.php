<?php
session_start();
require_once 'config.php';

// Проверяем, что данные пришли
if (empty($_GET)) {
    header('Location: index.php');
    exit;
}

// Получаем данные
$full_name = trim($_GET['full_name'] ?? '');
$phone = trim($_GET['phone'] ?? '');
$email = trim($_GET['email'] ?? '');
$birth_date = trim($_GET['birth_date'] ?? '');
$gender = $_GET['gender'] ?? '';
$languages = $_GET['languages'] ?? [];
$biography = trim($_GET['biography'] ?? '');
$contract_accepted = isset($_GET['contract_accepted']) ? 1 : 0;

// Простая валидация
$errors = [];
if (empty($full_name)) $errors[] = "ФИО обязательно";
if (empty($phone)) $errors[] = "Телефон обязателен";
if (empty($email)) $errors[] = "Email обязателен";
if (empty($birth_date)) $errors[] = "Дата рождения обязательна";
if (empty($gender)) $errors[] = "Выберите пол";
if (empty($languages)) $errors[] = "Выберите язык";
if (!$contract_accepted) $errors[] = "Примите контракт";

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "❌ $error<br>";
    }
    echo '<a href="index.php">Назад</a>';
    exit;
}

// Сохраняем в БД
try {
    $sql = "INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$full_name, $phone, $email, $birth_date, $gender, $biography, $contract_accepted]);
    $app_id = $pdo->lastInsertId();

    // Генерируем логин и пароль
    $login = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $full_name), 0, 6)) . '_' . rand(100, 999);
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->prepare("UPDATE applications SET login = ?, password_hash = ? WHERE id = ?")->execute([$login, $hash, $app_id]);

    // Сохраняем языки
    $langs = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
    $langStmt = $pdo->prepare("SELECT id FROM programming_languages WHERE name = ?");
    $linkStmt = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    
    foreach ($languages as $lang) {
        $langStmt->execute([$lang]);
        $row = $langStmt->fetch();
        if ($row) {
            $linkStmt->execute([$app_id, $row['id']]);
        }
    }

    // Авторизуем пользователя
    $_SESSION['user_id'] = $app_id;
    $_SESSION['user_name'] = $full_name;

    header("Location: index.php?new_login=" . urlencode($login) . "&new_password=" . urlencode($password));

} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}