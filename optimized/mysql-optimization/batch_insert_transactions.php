<?php
$host = '127.0.0.1';
$db   = 'analytics_opt';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$data = 1000;
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $rows = [];
    for ($i = 0; $i < 1000; $i++) {
        $user_id = rand(1, 20);
        $page_id = rand(1, 10);
        $country = ['UA','PL','DE','FR'][rand(0,3)];
        $device_id = rand(1,4);
        $created_at = date('Y-m-d H:i:s', strtotime("-".rand(0,10)." days"));
        $duration_ms = rand(100, 1000);
        $rows[] = [
            $user_id,
            $page_id,
            $country,
            $device_id,
            $created_at,
            $duration_ms
        ];
    }
    $batchSize = 200;
    $pdo->beginTransaction();
    for ($i = 0; $i < count($rows); $i += $batchSize) {
        $batch = array_slice($rows, $i, $batchSize);

        $placeholders = [];
        $values = [];
        foreach ($batch as $row) {
            $placeholders[] = "(?, ?, ?, ?, ?, ?)";
            $values = array_merge($values, $row);
        }

        $sql = "INSERT INTO pv_events (user_id, page_id, country, device_id, created_at, duration_ms) 
                VALUES ".implode(',', $placeholders);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }
    $pdo->commit();

    echo "Вставлено " . $data . " рядків!";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
