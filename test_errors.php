<?php
// 1. Force errors to show on screen instantly
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP Engine Active. Loading index.php...</h1><hr>";

// 2. Pull in your main index file to catch its crash
require('index.php');
?>