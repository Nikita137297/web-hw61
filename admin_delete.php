<?php
require_once 'config.php';
authenticateAdmin();

global $pdo, $table_apps;

$id = (int)$_GET['id'];
if ($id) {
    $pdo->prepare("DELETE FROM $table_apps WHERE id = ?")->execute([$id]);
}
header('Location: admin.php');