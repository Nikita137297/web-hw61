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

$count = $pdo->query("SELECT COUNT(*) FROM $table_apps")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкеты</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container { max-width: 1200px; }
        .debug-box { background: #fff3cd; padding: 0.8rem 1.2rem; border-radius: 12px; margin-bottom: 1.5rem; border-left: 4px solid #ffc107; }
        .debug-box strong { color: #856404; }
        .empty-state { text-align: center; padding: 3rem; color: #6a1b9a; }
        .empty-state .big { font-size: 3rem; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>📡 Программно-аппаратные средства Web</h1>
            <p class="student-info">📋 Список анкет</p>
        </div>
    </header>

    <main class="container">
        <div class="debug-box">
            <strong>ℹ️ Отладка:</strong> В таблице <strong><?php echo $table_apps; ?></strong> записей: <strong><?php echo $count; ?></strong>
        </div>

        <?php if (empty($apps)): ?>
            <div class="empty-state">
                <div class="big">😕</div>
                <h2>Нет анкет</h2>
                <p style="color:#7b5a8a;">Пока никто не заполнил форму</p>
                <a href="index.php" class="action-btn" style="margin-top:1rem; display:inline-block;">📝 Заполнить анкету</a>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="applications-table">
                    <thead>
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
                    </thead>
                    <tbody>
                        <?php foreach ($apps as $app): ?>
                        <tr>
                            <td style="font-weight:700; color:#4a148c;"><?php echo $app['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($app['full_name']); ?></strong></td>
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
                                <a href="admin_edit.php?id=<?php echo $app['id']; ?>" class="btn-admin-edit" style="background:linear-gradient(135deg,#4caf50,#2e7d32);color:white;padding:0.3rem 0.8rem;border-radius:30px;text-decoration:none;font-size:0.75rem;display:inline-block;">✏️</a>
                                <a href="admin_delete.php?id=<?php echo $app['id']; ?>" class="btn-admin-delete" style="background:linear-gradient(135deg,#e91e63,#c2185b);color:white;padding:0.3rem 0.8rem;border-radius:30px;text-decoration:none;font-size:0.75rem;display:inline-block;border:none;cursor:pointer;" onclick="return confirm('Удалить?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <a href="index.php" class="action-btn">📝 Форма</a>
            <a href="admin.php" class="action-btn secondary">👑 Админ-панель</a>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Лабораторная работа №6 | Май 2026</p>
        </div>
    </footer>
</body>
</html>