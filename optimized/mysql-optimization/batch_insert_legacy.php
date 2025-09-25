<?php
$host = '127.0.0.1';
$db   = 'analytics_legacy';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$rowsToInsert = 500;
$data = [];
for ($i = 1; $i <= $rowsToInsert; $i++) {
    $userId = rand(1, 50);
    $url = '/product/' . rand(1, 100);
    $country = ['UA','PL','US'][rand(0,2)];
    $device = ['desktop','mobile'][rand(0,1)];
    $createdAt = date('Y-m-d H:i:s', strtotime("2025-09-15") + rand(0, 86400*3));
    $durationMs = rand(50, 2000);
    $data[] = [$userId, $url, $country, $device, $createdAt, $durationMs];
}


$batchSize = 100;
$chunks = array_chunk($data, $batchSize);

$pdo->beginTransaction();
try {
    foreach ($chunks as $chunk) {
        $placeholders = [];
        $values = [];
        foreach ($chunk as $row) {
            $placeholders[] = "(?, ?, ?, ?, ?, ?)";
            $values = array_merge($values, $row);
        }
        $sql = "INSERT INTO pv (user_id, url, country, device, created_at, duration_ms) 
                VALUES " . implode(',', $placeholders);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
    $pdo->commit();
    echo "Вставлено " . count($data) . " рядків!";
} catch (\PDOException $e) {
    $pdo->rollBack();
    die("Помилка вставки: " . $e->getMessage());
}
