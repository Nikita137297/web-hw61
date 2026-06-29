<?php
require_once 'config.php';
$adminLogin = authenticateAdmin();

$tableApps = TABLE_APPLICATIONS;
$tableAppLangs = TABLE_APP_LANGS;
$tableLangs = TABLE_LANGUAGES;

$id = (int)$_GET['id'];
$app = $pdo->prepare("SELECT * FROM $tableApps WHERE id = ?");
$app->execute([$id]);
$app = $app->fetch();

if (!$app) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $birth_date = trim($_POST['birth_date']);
    $gender = $_POST['gender'];
    $biography = trim($_POST['biography']);
    $contract_accepted = isset($_POST['contract_accepted']) ? 1 : 0;
    $languages = $_POST['languages'] ?? [];

    $sql = "UPDATE $tableApps SET full_name=?, phone=?, email=?, birth_date=?, gender=?, biography=?, contract_accepted=? WHERE id=?";
    $pdo->prepare($sql)->execute([$full_name, $phone, $email, $birth_date, $gender, $biography, $contract_accepted, $id]);

    $pdo->prepare("DELETE FROM $tableAppLangs WHERE application_id = ?")->execute([$id]);
    
    $langStmt = $pdo->prepare("SELECT id FROM $tableLangs WHERE name = ?");
    $linkStmt = $pdo->prepare("INSERT INTO $tableAppLangs (application_id, language_id) VALUES (?, ?)");
    foreach ($languages as $lang) {
        $langStmt->execute([$lang]);
        $row = $langStmt->fetch();
        if ($row) $linkStmt->execute([$id, $row['id']]);
    }

    header('Location: admin.php');
    exit;
}

$userLangs = $pdo->prepare("SELECT pl.name FROM $tableAppLangs al JOIN $tableLangs pl ON al.language_id = pl.id WHERE al.application_id = ?");
$userLangs->execute([$id]);
$userLangs = $userLangs->fetchAll(PDO::FETCH_COLUMN);

$allLangs = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактирование</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .container { max-width: 600px; margin: 30px auto; background: white; padding: 30px; border-radius: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px; }
        .btn { background: #4caf50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #388e3c; }
        select[multiple] { min-height: 120px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ Редактирование</h1>
        <form method="POST">
            <div class="form-group">
                <label>ФИО *</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($app['full_name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон *</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($app['phone']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($app['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Дата рождения *</label>
                <input type="date" name="birth_date" value="<?php echo $app['birth_date']; ?>" required>
            </div>
            <div class="form-group">
                <label>Пол *</label>
                <input type="radio" name="gender" value="male" <?php echo $app['gender'] == 'male' ? 'checked' : ''; ?>> Мужской
                <input type="radio" name="gender" value="female" <?php echo $app['gender'] == 'female' ? 'checked' : ''; ?>> Женский
            </div>
            <div class="form-group">
                <label>Языки *</label>
                <select name="languages[]" multiple required>
                    <?php foreach ($allLangs as $lang): ?>
                        <option value="<?php echo $lang; ?>" <?php echo in_array($lang, $userLangs) ? 'selected' : ''; ?>>
                            <?php echo $lang; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Биография</label>
                <textarea name="biography" rows="4"><?php echo htmlspecialchars($app['biography'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="contract_accepted" value="1" <?php echo $app['contract_accepted'] ? 'checked' : ''; ?>>
                    Контракт принят
                </label>
            </div>
            <button type="submit" class="btn">💾 Сохранить</button>
            <a href="admin.php" style="margin-left:10px;">Отмена</a>
        </form>
    </div>
</body>
</html>