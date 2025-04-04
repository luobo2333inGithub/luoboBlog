<?php 
require_once 'config.php'; 
require_once 'functions.php'; 
 
header('Content-Type: text/plain');
 
if (!isset($_GET['text'])) {
    echo '';
    exit;
}
 
$text = trim($_GET['text']);
echo generate_slug($text);
?>