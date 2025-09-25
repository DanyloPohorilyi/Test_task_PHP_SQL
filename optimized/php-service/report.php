<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "Logger.php";
class ReportApp {
    private array $orders;
    private string $outFile;
    private Logger $logger;
    
    private array $validOrders = [];
    private int $count = 0;
    private float $totalPaid = 0.0;
    private float $avgAmount = 0.0;
    public function __construct(array $orders, string $file, ?Logger $logger = null)
    {
        $this->orders = $orders;
        $this->outFile = $file;
        $this->logger = $logger ?? new Logger(__DIR__ . DIRECTORY_SEPARATOR . 'report.log');
        $this->logger->info("ReportApp створено. Файл: {$this->outFile}");
    }
    public function process():void {
        $this->logger->info("Обробка замовлень (кількість: ".count($this->orders).")");
        $this->validOrders = array_filter($this->orders, function ($o) {
            return isset($o['status'], $o['amount']) 
                && $o['status'] === 'paid'
                && is_numeric($o['amount'])
                && $o['amount'] > 0;
        });
        $this->count = count($this->validOrders);
        $this->totalPaid = array_reduce($this->validOrders, function ($carry, $o) {
            return $carry + (float)$o['amount'];
        }, 0.0);
        $this->avgAmount = $this->count > 0 ? $this->totalPaid / $this->count : 0.0;
        $this->logger->info("Дійсних замовлень: {$this->count}, Всього: {$this->totalPaid}, Середнє: {$this->avgAmount}");
    }
    private function formatNumber(float $n): string
    {
        if (floor($n) == $n) {
            return (string)(int)$n;
        }
        return number_format($n, 2, '.', '');
    }

    public function write(): void
    {
        $this->logger->info("Звіт знаходиться: {$this->outFile}");

        $dir = dirname($this->outFile);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                $this->logger->error("Не вдається створити папку: $dir");
                throw new RuntimeException("Не вдається створити папку: $dir");
            }
        }

        $lines = [
            "Start report",
            "Valid orders: {$this->count}",
            "Total paid: " . $this->formatNumber($this->totalPaid),
            "Avg amount: " . ($this->count > 0 ? $this->formatNumber($this->avgAmount) : 'N/A'),
        ];
        $content = implode(PHP_EOL, $lines) . PHP_EOL;

        $tmp = tempnam($dir, 'rep_');
        if ($tmp === false) {
            $this->logger->error("Не вдається створити файл у папці $dir");
            throw new RuntimeException("Не вдається створити файл у папці $dir");
        }

        $fp = @fopen($tmp, 'c');
        if ($fp === false) {
            @unlink($tmp);
            $this->logger->error("Не відкривається файл: $tmp");
            throw new RuntimeException("Не відкривається файл: $tmp");
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            @unlink($tmp);
            $this->logger->error("Не вдалося заблокувати файл: $tmp");
            throw new RuntimeException("Не вдалося заблокувати файл: $tmp");
        }

        ftruncate($fp, 0);
        rewind($fp);
        $written = fwrite($fp, $content);
        if ($written === false) {
            flock($fp, LOCK_UN);
            fclose($fp);
            @unlink($tmp);
            $this->logger->error("Не вдалося записати у файл: $tmp");
            throw new RuntimeException("Не вдалося записати у файл: $tmp");
        }

        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        @chmod($tmp, 0644);

        if (!@rename($tmp, $this->outFile)) {
            @unlink($tmp);
            $this->logger->error("Не вдалося перейменувати $tmp у {$this->outFile}");
            throw new RuntimeException("Не вдалося перейменувати $tmp у {$this->outFile}");
        }

        $this->logger->info("Звіт успішно записаний у {$this->outFile}");
    }
    public function summary(): void
    {
        echo "Start report" . PHP_EOL;
        echo "Valid orders: {$this->count}" . PHP_EOL;
        echo "Total paid: " . $this->formatNumber($this->totalPaid) . PHP_EOL;
        echo "Avg amount: " . ($this->count > 0 ? $this->formatNumber($this->avgAmount) : 'N/A') . PHP_EOL;
    }
    public function __destruct()
    {
        $this->logger->info("ReportApp завершив роботу.");
    }


}

$orders = [
    ["id" => 1, "user" => "Ivan", "amount" => 100, "status" => "paid"],
    ["id" => 2, "user" => "Oksana", "amount" => -50, "status" => "paid"], // аномалія
    ["id" => 3, "user" => "Ivan", "amount" => 200, "status" => "pending"], // не враховується
    ["id" => 4, "user" => "Petro", "amount" => 300, "status" => "paid"],

    ["id" => 5, "user" => "Anna", "amount" => 150, "status" => "paid"],
    ["id" => 6, "user" => "Serhiy", "amount" => 0, "status" => "paid"], // аномалія
    ["id" => 7, "user" => "Olena", "amount" => 250, "status" => "paid"],
    ["id" => 8, "user" => "Dmytro", "amount" => 120, "status" => "pending"], // не враховується
    ["id" => 9, "user" => "Kateryna", "amount" => 180, "status" => "paid"],
    ["id" => 10, "user" => "Andriy", "amount" => -20, "status" => "paid"], // аномалія
    ["id" => 11, "user" => "Sofia", "amount" => 90, "status" => "paid"],
    ["id" => 12, "user" => "Mykola", "amount" => 400, "status" => "paid"],
    ["id" => 13, "user" => "Liliya", "amount" => 50, "status" => "pending"], // не враховується
    ["id" => 14, "user" => "Yuriy", "amount" => 300, "status" => "paid"],
];

$outFile = __DIR__ . DIRECTORY_SEPARATOR . 'report.txt';
$logger = new Logger(__DIR__ . DIRECTORY_SEPARATOR . 'report.log');

try {
    $app = new ReportApp($orders, $outFile, $logger);
    $app->process();
    $app->write();
    $app->summary();
} catch (Throwable $e) {
    $logger->error("Необроблене виключення: " . $e->getMessage());
    echo "Виникла помилка: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
