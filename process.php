<?php
session_start();
require_once 'config.php';

global $pdo, $table_apps, $table_langs, $table_app_langs;

if (empty($_GET)) {
    header('Location: index.php');
    exit;
}

$full_name = trim($_GET['full_name'] ?? '');
$phone = trim($_GET['phone'] ?? '');
$email = trim($_GET['email'] ?? '');
$birth_date = trim($_GET['birth_date'] ?? '');
$gender = $_GET['gender'] ?? '';
$languages = $_GET['languages'] ?? [];
$biography = trim($_GET['biography'] ?? '');
$contract_accepted = isset($_GET['contract_accepted']) ? 1 : 0;

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

try {
    $sql = "INSERT INTO $table_apps (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$full_name, $phone, $email, $birth_date, $gender, $biography, $contract_accepted]);
    $app_id = $pdo->lastInsertId();

    $login = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $full_name), 0, 6)) . '_' . rand(100, 999);
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 10);
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->prepare("UPDATE $table_apps SET login = ?, password_hash = ? WHERE id = ?")->execute([$login, $hash, $app_id]);

    $langStmt = $pdo->prepare("SELECT id FROM $table_langs WHERE name = ?");
    $linkStmt = $pdo->prepare("INSERT INTO $table_app_langs (application_id, language_id) VALUES (?, ?)");
    
    foreach ($languages as $lang) {
        $langStmt->execute([$lang]);
        $row = $langStmt->fetch();
        if ($row) {
            $linkStmt->execute([$app_id, $row['id']]);
        }
    }

    $_SESSION['user_id'] = $app_id;
    $_SESSION['user_name'] = $full_name;

    header("Location: index.php?new_login=" . urlencode($login) . "&new_password=" . urlencode($password));

} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}