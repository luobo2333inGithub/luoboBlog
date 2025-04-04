<?php 
require_once '../includes/config.php'; 
require_once '../includes/functions.php'; 
 
if (!is_logged_in()) {
    header("Location: ../login.php"); 
    exit;
}
 
// 设置备份文件名 
$backup_file = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
 
// 获取所有表 
$tables = [];
$result = $pdo->query('SHOW TABLES');
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
}
 
// 开始备份 
$output = "-- MySQL Backup\n";
$output .= "-- Host: " . DB_HOST . "\n";
$output .= "-- Database: " . DB_NAME . "\n";
$output .= "-- Backup Time: " . date('Y-m-d H:i:s') . "\n\n";
 
foreach ($tables as $table) {
    // 表结构 
    $output .= "--\n-- Table structure for table `$table`\n--\n";
    $output .= "DROP TABLE IF EXISTS `$table`;\n";
    
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
    $output .= $create[1] . ";\n\n";
    
    // 表数据 
    $output .= "--\n-- Data for table `$table`\n--\n";
    
    $rows = $pdo->query("SELECT * FROM `$table`");
    while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
        $output .= "INSERT INTO `$table` VALUES(";
        $values = [];
        foreach ($row as $value) {
            $values[] = is_null($value) ? 'NULL' : $pdo->quote($value);
        }
        $output .= implode(',', $values) . ");\n";
    }
    $output .= "\n";
}
 
// 保存备份文件 
file_put_contents('../backups/' . $backup_file, $output);
 
// 下载备份 
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $backup_file . '"');
readfile('../backups/' . $backup_file);
exit;
?>