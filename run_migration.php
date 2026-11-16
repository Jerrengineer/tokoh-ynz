<?php
require_once 'config.php';
check_login();
require_admin(); // only admin can run migrations

$schemaFile = __DIR__ . '/db/schema.sql';
if (!file_exists($schemaFile)) {
    die('Schema file not found.');
}

$sql = file_get_contents($schemaFile);
// Remove comments and split by semicolon
$statements = array_filter(array_map('trim', explode(';', $sql)));
$success = [];
$error = [];
foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    // Skip lines that start with --
    if (strpos(ltrim($stmt), '--') === 0) continue;
    $result = $conn->query($stmt);
    if ($result === true) {
        $success[] = $stmt;
    } else {
        $error[] = $conn->error . " (SQL: $stmt)";
    }
}

echo "Migration completed.<br>";
if (!empty($success)) {
    echo "Executed " . count($success) . " statements successfully.<br>";
}
if (!empty($error)) {
    echo "Errors encountered:<br>" . implode('<br>', $error);
}
?>
