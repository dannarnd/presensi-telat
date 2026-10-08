<?php

$mysql_host = '127.0.0.1';
$mysql_db   = 'presensi_telat';
$mysql_user = 'root';
$mysql_pass = '';

$pg_host = 'aws-0-ap-southeast-1.pooler.supabase.com';
$pg_port = '6543';
$pg_db   = 'postgres';
$pg_user = 'postgres.mjstuldrmlsqacjvivcu';
$pg_pass = 'D4nil4ri4nd40904';

try {
    echo "Connecting to local MySQL...\n";
    $mysql = new PDO("mysql:host=$mysql_host;dbname=$mysql_db;charset=utf8", $mysql_user, $mysql_pass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connecting to remote PostgreSQL...\n";
    $pg = new PDO("pgsql:host=$pg_host;port=$pg_port;dbname=$pg_db;sslmode=require", $pg_user, $pg_pass);
    $pg->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Disable foreign key checks for pgsql temporarily by deferring constraints if possible, 
    // or just copy in correct order.
    // Order: users, school_classes, students, delay_logs, counseling_logs

    $tables = [
        'users',
        'school_classes',
        'students',
        'delay_logs',
        'counseling_logs'
    ];

    foreach ($tables as $table) {
        echo "Copying table: $table...\n";
        
        $stmt = $mysql->query("SELECT * FROM `$table`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($rows)) {
            echo "  No data.\n";
            continue;
        }

        // Empty remote table first
        $pg->exec("TRUNCATE TABLE \"$table\" CASCADE");

        $columns = array_keys($rows[0]);
        $colNames = implode(', ', array_map(fn($c) => "\"$c\"", $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $insertStmt = $pg->prepare("INSERT INTO \"$table\" ($colNames) VALUES ($placeholders)");

        $count = 0;
        foreach ($rows as $row) {
            $values = array_values($row);
            $insertStmt->execute($values);
            $count++;
        }
        echo "  Copied $count rows.\n";
    }

    echo "Data migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
