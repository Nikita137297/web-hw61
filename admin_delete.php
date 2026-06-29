<?php
require_once 'config.php';
authenticateAdmin();

$id = (int)$_GET['id'];
if ($id) {
    $table = TABLE_APPLICATIONS;
    $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
}
header('Location: admin.php');