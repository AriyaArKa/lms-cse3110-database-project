<?php
/**
 * Database Setup Script
 * Run this file ONCE to create all database objects
 * Usage: php setup_database.php OR visit http://localhost/lms_db/setup_database.php
 */

try {
    // Connect to MySQL
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server...\n";

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS university_db");
    $pdo->exec("USE university_db");

    echo "Database 'university_db' ready...\n";

    // Read the setup.sql file
    $sql = file_get_contents(__DIR__ . '/sql/setup.sql');

    // Remove DELIMITER commands and split into individual statements
    $sql = str_replace('DELIMITER //', '', $sql);
    $sql = str_replace('DELIMITER ;', '', $sql);
    $sql = str_replace('//', ';', $sql);

    // Split by semicolon but keep the statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $successCount = 0;
    $errorCount = 0;

    foreach ($statements as $statement) {
        if (empty($statement))
            continue;

        try {
            $pdo->exec($statement);
            $successCount++;

            // Show what was created
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE (?:IF NOT EXISTS )?`?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "✓ Created table: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'CREATE VIEW') !== false) {
                preg_match('/CREATE (?:OR REPLACE )?VIEW `?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "✓ Created view: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'CREATE PROCEDURE') !== false) {
                preg_match('/CREATE PROCEDURE `?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "✓ Created procedure: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'CREATE FUNCTION') !== false) {
                preg_match('/CREATE FUNCTION `?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "✓ Created function: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'CREATE TRIGGER') !== false) {
                preg_match('/CREATE TRIGGER `?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "✓ Created trigger: {$matches[1]}\n";
                }
            } elseif (stripos($statement, 'INSERT INTO') !== false) {
                preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches);
                if (isset($matches[1])) {
                    echo "✓ Inserted sample data into: {$matches[1]}\n";
                }
            }
        } catch (PDOException $e) {
            // Skip errors for "already exists" or "duplicate entry"
            if (
                strpos($e->getMessage(), 'already exists') === false &&
                strpos($e->getMessage(), 'Duplicate entry') === false
            ) {
                $errorCount++;
                echo "✗ Error: " . $e->getMessage() . "\n";
                echo "  Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }

    echo "\n" . str_repeat("=", 50) . "\n";
    echo "DATABASE SETUP COMPLETE!\n";
    echo str_repeat("=", 50) . "\n";
    echo "Successful operations: $successCount\n";
    echo "Errors (if any): $errorCount\n";
    echo "\nYou can now visit: http://localhost/lms_db/sql_operations.php\n";
    echo str_repeat("=", 50) . "\n";

} catch (PDOException $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>