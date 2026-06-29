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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкеты</title>
    <link rel="stylesheet" href="style.css">  <!-- ЭТО ДОБАВИТЬ! -->
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