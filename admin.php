<?php
require_once 'config.php';
$adminLogin = authenticateAdmin();

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
    <title>Админ-панель</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; }
        .header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .stats { display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0; }
        .stat { background: #f3e5f5; padding: 15px; border-radius: 10px; min-width: 100px; text-align: center; }
        .stat .num { font-size: 24px; font-weight: bold; color: #4a148c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #7b1fa2; color: white; }
        .badge { background: #7b1fa2; color: white; padding: 3px 10px; border-radius: 10px; font-size: 12px; display: inline-block; margin: 2px; }
        .btn { background: #4caf50; color: white; padding: 5px 12px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-danger { background: #f44336; }
        .nav a { margin-right: 10px; color: #7b1fa2; }
        .admin-badge { background: #4caf50; color: white; padding: 5px 15px; border-radius: 20px; }
        .debug { background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👑 Админ-панель (hw6)</h1>
            <div>
                <span class="admin-badge">✅ <?php echo htmlspecialchars($adminLogin); ?></span>
            </div>
        </div>

        <div class="debug">
            <strong>Отладка:</strong> В таблице <strong><?php echo $table_apps; ?></strong> записей: <strong><?php echo $count; ?></strong>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="num"><?php echo count($apps); ?></div>
                <div>Всего анкет</div>
            </div>
            <?php foreach ($stats as $s): ?>
            <div class="stat">
                <div class="num"><?php echo $s['count']; ?></div>
                <div><?php echo htmlspecialchars($s['name']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <h2>Все анкеты</h2>
        <?php if (empty($apps)): ?>
            <p style="color:red;">❌ Нет анкет</p>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Телефон</th>
                <th>Email</th>
                <th>Языки</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($apps as $app): ?>
            <tr>
                <td><?php echo $app['id']; ?></td>
                <td><?php echo htmlspecialchars($app['full_name']); ?></td>
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
                <td>
                    <a href="admin_edit.php?id=<?php echo $app['id']; ?>" class="btn">✏️</a>
                    <a href="admin_delete.php?id=<?php echo $app['id']; ?>" class="btn btn-danger" onclick="return confirm('Удалить?')">🗑️</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>

        <div class="nav" style="margin-top:20px;">
            <a href="index.php">📝 Форма</a>
            <a href="list.php">📋 Анкеты</a>
        </div>
    </div>
</body>
</html>