<?php

function getDBConnection() {
    $envFile = __DIR__ . '/../.env';

    if (!file_exists($envFile)) {
        throw new Exception('.env file not found');
    }

    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);

    $supabaseUrl = '';
    $supabaseKey = '';

    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'VITE_SUPABASE_URL=') === 0) {
            $supabaseUrl = trim(substr($line, strlen('VITE_SUPABASE_URL=')));
        }
        if (strpos($line, 'VITE_SUPABASE_SUPABASE_ANON_KEY=') === 0) {
            $supabaseKey = trim(substr($line, strlen('VITE_SUPABASE_SUPABASE_ANON_KEY=')));
        }
    }

    if (empty($supabaseUrl)) {
        throw new Exception('Supabase URL not found in .env file');
    }

    preg_match('/https:\/\/([^.]+)\.supabase\.co/', $supabaseUrl, $matches);

    if (!isset($matches[1])) {
        throw new Exception('Invalid Supabase URL format');
    }

    $projectRef = $matches[1];

    $host = "aws-0-us-east-1.pooler.supabase.com";
    $port = "6543";
    $dbname = "postgres";
    $user = "postgres." . $projectRef;
    $password = "makevi2025secure";

    try {
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);

        return $pdo;
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}
