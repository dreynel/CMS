<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDB();
echo "CONNECTED: " . get_class($pdo) . "\n";
unlink(__FILE__);

