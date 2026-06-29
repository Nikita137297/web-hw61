<?php
$host = 'localhost';
$dbname = 'u82087';
$username = 'u82087';
$password = '3565896';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка БД: " . $e->getMessage());
}

// ----- ТАБЛИЦЫ ДЛЯ 6-ГО ЗАДАНИЯ (с суффиксом _hw6) -----
define('TABLE_APPLICATIONS', 'applications_hw6');
define('TABLE_LANGUAGES', 'programming_languages_hw6');
define('TABLE_APP_LANGS', 'application_languages_hw6');
define('TABLE_ADMINS', 'admins_hw6');

function authenticateAdmin() {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Admin"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
    global $pdo;
    $table = TABLE_ADMINS;
    $stmt = $pdo->prepare("SELECT password_hash FROM $table WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        header('WWW-Authenticate: Basic realm="Admin"');
        header('HTTP/1.0 401 Unauthorized');
        exit;
    }
    return $_SERVER['PHP_AUTH_USER'];
}