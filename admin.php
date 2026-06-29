<?php
session_start();
require_once 'config.php';

// HTTP-авторизация
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit;
}

global $pdo, $table_admins;
$stmt = $pdo->prepare("SELECT password_hash FROM $table_admins WHERE login = ?");
$stmt->execute([$_SERVER['PHP_AUTH_USER']]);
$admin = $stmt->fetch();
if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
    header('WWW-Authenticate: Basic realm="Admin Area"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Неверный логин или пароль';
    exit;
}

$adminLogin = $_SERVER['PHP_AUTH_USER'];

global $pdo, $table_apps, $table_app_langs, $table_langs;

$stats = $pdo->query("
    SELECT pl.name, COUNT(al.application_id) as count 
    FROM $table_langs pl
    LEFT JOIN $table_app_langs al ON pl.id = al.language_id
    GROUP BY pl.id
")->fetchAll();

$apps = $pdo->query("
    SELECT a.*, GROUP_CONCAT(pl.name) as languages 
    FROM $table_apps a
    LEFT JOIN $table_app_langs al ON a.id = al.application_id
    LEFT JOIN $table_langs pl ON al.language_id = pl.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
")->fetchAll();

$count = $pdo->query("SELECT COUNT(*) FROM $table_apps")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 1400px; margin: 0 auto; padding: 0 20px; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; background: rgba(255,255,255,0.92); padding: 1.2rem 2rem; border-radius: 20px; margin-bottom: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid rgba(123,31,162,0.08); }
        .admin-header .admin-badge { background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; padding: 0.3rem 1.2rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.2rem; margin-bottom: 2rem; }
        .stat-card { background: rgba(255,255,255,0.92); padding: 1.2rem; border-radius: 18px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid rgba(123,31,162,0.06); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-card .number { font-size: 2.2rem; font-weight: 700; color: #4a148c; }
        .stat-card .label { color: #6a1b9a; font-size: 0.9rem; font-weight: 500; margin-top: 0.2rem; }
        .stat-card .lang-bar { margin-top: 0.6rem; height: 6px; background: #e1bee7; border-radius: 10px; overflow: hidden; }
        .stat-card .lang-bar .fill { height: 100%; background: linear-gradient(90deg, #7b1fa2, #4a148c); border-radius: 10px; transition: width 0.8s ease; }
        .table-wrapper { overflow-x: auto; background: rgba(255,255,255,0.92); border-radius: 20px; padding: 1.2rem; box-shadow: 0 8px 40px rgba(123,31,162,0.06); border: 1px solid rgba(123,31,162,0.06); }
        .admin-table { width: 100%; border-collapse: collapse; min-width: 900px; }
        .admin-table th { background: linear-gradient(135deg, #7b1fa2, #4a148c); color: white; padding: 0.8rem 1rem; text-align: left; font-weight: 600; }
        .admin-table th:first-child { border-radius: 12px 0 0 0; }
        .admin-table th:last-child { border-radius: 0 12px 0 0; }
        .admin-table td { padding: 0.8rem 1rem; border-bottom: 1px solid #f3e5f5; vertical-align: middle; }
        .admin-table tr:hover { background: #f3e5f5; }
        .admin-table .badge { background: linear-gradient(135deg, #7b1fa2, #4a148c); color: white; padding: 0.2rem 0.7rem; border-radius: 50px; font-size: 0.7rem; display: inline-block; margin: 0.1rem; }
        .btn-admin-edit { background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; padding: 0.3rem 0.8rem; border-radius: 30px; text-decoration: none; font-size: 0.75rem; display: inline-block; transition: all 0.3s; }
        .btn-admin-edit:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(76,175,80,0.3); }
        .btn-admin-delete { background: linear-gradient(135deg, #e91e63, #c2185b); color: white; padding: 0.3rem 0.8rem; border-radius: 30px; text-decoration: none; font-size: 0.75rem; display: inline-block; border: none; cursor: pointer; transition: all 0.3s; }
        .btn-admin-delete:hover { transform: scale(1.05); box-shadow: 0 4px 15px rgba(233,30,99,0.3); }
        .empty-state { text-align: center; padding: 3rem; color: #6a1b9a; }
        .admin-container h3 { color: #4a148c; margin-bottom: 1rem; }
        .admin-container .btn-back { background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; padding: 0.7rem 1.5rem; border-radius: 50px; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.3s; }
        .admin-container .btn-back:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(76,175,80,0.3); }
        .admin-container h2 { color: #4a148c; }
        header { background: linear-gradient(135deg, #7b1fa2, #4a148c); color: #fff; padding: 2.5rem 0; text-align: center; border-bottom: 5px solid #4caf50; }
        footer { background: linear-gradient(135deg, #7b1fa2, #4a148c); color: #fff; text-align: center; padding: 1.5rem 0; margin-top: 3rem; border-top: 5px solid #4caf50; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="student-info">👑 Админ-панель</p>
        </div>
    </header>

    <main class="admin-container">
        <div class="admin-header">
            <div>
                <span style="font-size:1.8rem;">👑</span>
                <span style="font-weight:600; font-size:1.1rem; margin-left:0.5rem;">Администратор</span>
                <span class="admin-badge" style="margin-left:1rem;">✅ <?php echo htmlspecialchars($adminLogin); ?></span>
            </div>
            <div>
                <a href="index.php" class="btn-back" style="margin-right:0.5rem;">📝 Форма</a>
                <a href="list.php" class="btn-back">📋 Анкеты</a>
            </div>
        </div>

        <h3 style="color:#4a148c; margin-bottom:1rem;">📊 Статистика</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?php echo count($apps); ?></div>
                <div class="label">Всего анкет</div>
            </div>
            <?php foreach ($stats as $s): ?>
            <div class="stat-card">
                <div class="number" style="font-size:1.8rem;"><?php echo $s['count']; ?></div>
                <div class="label"><?php echo htmlspecialchars($s['name']); ?></div>
                <div class="lang-bar">
                    <div class="fill" style="width: <?php echo $count > 0 ? ($s['count'] / $count * 100) : 0; ?>%;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <h3 style="color:#4a148c; margin-bottom:1rem;">📋 Все анкеты</h3>
        <div class="table-wrapper">
            <?php if (empty($apps)): ?>
                <div class="empty-state">
                    <p style="font-size:1.5rem;">😕 Нет анкет</p>
                    <p style="color:#7b5a8a;">Пока никто не заполнил форму</p>
                </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Языки</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($apps as $app): ?>
                    <tr>
                        <td style="font-weight:600; color:#4a148c;"><?php echo $app['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($app['full_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($app['phone']); ?></td>
                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                        <td>
                            <?php 
                            $langs = explode(',', $app['languages'] ?? '');
                            foreach ($langs as $lang): 
                                if (trim($lang)):
                            ?>
                                <span class="badge"><?php echo trim($lang); ?></span>
                            <?php endif; endforeach; ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="admin_edit.php?id=<?php echo $app['id']; ?>" class="btn-admin-edit">✏️ Редактировать</a>
                            <a href="admin_delete.php?id=<?php echo $app['id']; ?>" class="btn-admin-delete" onclick="return confirm('Удалить анкету №<?php echo $app['id']; ?>?')">🗑️ Удалить</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Лабораторная работа №6 — Админ-панель | Май 2026</p>
        </div>
    </footer>
</body>
</html>