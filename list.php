<?php
require_once 'config.php';

global $pdo, $table_apps, $table_app_langs, $table_langs;

$stmt = $pdo->query("
    SELECT a.*, GROUP_CONCAT(pl.name) as languages 
    FROM $table_apps a
    LEFT JOIN $table_app_langs al ON a.id = al.application_id
    LEFT JOIN $table_langs pl ON al.language_id = pl.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
");
$apps = $stmt->fetchAll();

// Проверяем, есть ли записи
$count = $pdo->query("SELECT COUNT(*) FROM $table_apps")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Анкеты</title>
    <style>
        body { font-family: Arial; background: #f0f0f0; }
        .container { max-width: 1200px; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #7b1fa2; color: white; }
        tr:hover { background: #f5f5f5; }
        .badge { background: #7b1fa2; color: white; padding: 3px 10px; border-radius: 10px; font-size: 12px; display: inline-block; margin: 2px; }
        .nav { margin-top: 20px; }
        .nav a { margin-right: 10px; color: #7b1fa2; }
        .btn { background: #4caf50; color: white; padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-danger { background: #f44336; }
        .debug { background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #ffc107; }
        .debug strong { color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Анкеты (hw6)</h1>
        
        <div class="debug">
            <strong>Отладка:</strong> В таблице <strong><?php echo $table_apps; ?></strong> записей: <strong><?php echo $count; ?></strong>
        </div>
        
        <p>Всего анкет: <?php echo count($apps); ?></p>
        
        <?php if (empty($apps)): ?>
            <p style="color:red;">❌ Нет анкет в таблице <?php echo $table_apps; ?></p>
            <p>Проверьте, что вы заполнили форму и нажали "Сохранить".</p>
            <p><a href="index.php">📝 Заполнить анкету</a></p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Дата</th>
                    <th>Пол</th>
                    <th>Языки</th>
                    <th>Действия</th>
                </tr>
                <?php foreach ($apps as $app): ?>
                <tr>
                    <td><?php echo $app['id']; ?></td>
                    <td><?php echo htmlspecialchars($app['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($app['phone']); ?></td>
                    <td><?php echo htmlspecialchars($app['email']); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($app['birth_date'])); ?></td>
                    <td><?php echo $app['gender'] == 'male' ? '♂' : '♀'; ?></td>
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
        
        <div class="nav">
            <a href="index.php">📝 Форма</a>
            <a href="admin.php">👑 Админ-панель</a>
        </div>
    </div>
</body>
</html>